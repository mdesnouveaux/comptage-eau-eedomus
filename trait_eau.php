<?php
/*************************************************************************************/
/*                     ### Report conso Eau eedomus SQL V1.0 ###                     */
/*                                                                                   */
/*                       Developpement par Aurel@domo-blog.fr                        */
/*                                                                                   */
/*************************************************************************************/

include('parametres.php');

mysql_connect($server, $sqllogin, $sqlpass) OR die('Erreur de connexion à la base');
mysql_select_db('historique') OR die('Erreur de sélection de la base');

//-----------------------Import des données de consommation--------------------------
$requete = mysql_query('SELECT SUM(conso) FROM eau WHERE WEEK(date) = WEEK(curdate()) AND YEAR(date) = YEAR(curdate())') OR die('Erreur de la requête MySQL');
while ($resultat = mysql_fetch_row($requete)) {
    $consohebdo = $resultat[0];
}

$requete = mysql_query('SELECT SUM(conso) FROM eau WHERE MONTH(date) = MONTH(curdate()) AND YEAR(date) = YEAR(curdate())') OR die('Erreur de la requête MySQL');
while ($resultat = mysql_fetch_row($requete)) {
    $consomensuelle = $resultat[0];
}

$requete = mysql_query('SELECT SUM(conso) FROM eau WHERE YEAR(date) = YEAR(curdate())') OR die('Erreur de la requête MySQL');
while ($resultat = mysql_fetch_row($requete)) {
    $consoannuelle = $resultat[0];
}

//-----------------------Import des données de comparaison--------------------------
$requete = mysql_query('SELECT conso FROM eau ORDER BY id DESC');

while ($resultat = mysql_fetch_row($requete)) {
    $consoj1 = $resultat[0];
}
$requete = mysql_query('SELECT conso FROM eau ORDER BY id DESC LIMIT 1,1');
while ($resultat = mysql_fetch_row($requete)) {
    $consoj2 = $resultat[0];
}

mysql_close();


// conversion en m3
$consohebdom3 = ($consohebdo / 1000);
$consomensuellem3 = ($consomensuelle / 1000);
$consoannuellem3 = ($consoannuelle / 1000);

// Tarifs
$consohebdoprix = ($consohebdom3 * $prix_m3);
$consomensuelleprix = ($consomensuellem3 * $prix_m3);
$consoannuelleprix = ($consoannuellem3 * $prix_m3);

// Bilan
if ($consoj1 < $consoj2) {
    $bilan = '1';
} elseif ($consoj1 > $consoj2) {
    $bilan = '0';
}


//******************************************** Changement d'année ***********************************************
$annee_jour = date('Y');
$annee_veille = strftime("%Y", mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')));

if ($annee_jour > $annee_veille) {
    $consoannuellem3 = 0;
    $consoannuelleprix = 0;
}



//******************************************** Update conso hebdo***********************************************
$weeklyPriceParam = array_merge($bind, array(
    'periphId' => $periph_hebdo,
    'value'    => $consohebdoprix,
));

$url = str_replace(
    array_keys($weeklyPriceParam),
    array_values($weeklyPriceParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update hebdo: [" . $result . "]";
} else {
    echo "update hebdo ok<br/>";
}

//******************************************** Update conso mensuelle***********************************************
$monthlyPriceParam = array_merge($bind, array(
    'periphId' => $periph_mensuel,
    'value'    => $consomensuelleprix,
));

$url = str_replace(
    array_keys($monthlyPriceParam),
    array_values($monthlyPriceParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update mensuel: [" . $result . "]";
} else {
    echo "update mensuel ok<br/>";
}

//******************************************** Update conso annuelle***********************************************
$yearlyPriceParam = array_merge($bind, array(
    'periphId' => $periph_annuel,
    'value'    => $consoannuelleprix,
));

$url = str_replace(
    array_keys($yearlyPriceParam),
    array_values($yearlyPriceParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update annuel: [" . $result . "]";
} else {
    echo "update annuel ok<br/>";
}

//******************************************** Update conso hebdo m3***********************************************
$weeklyConsumptionParam = array_merge($bind, array(
    'periphId' => $periph_hebdom3,
    'value'    => $consohebdom3,
));

$url = str_replace(
    array_keys($weeklyConsumptionParam),
    array_values($weeklyConsumptionParam),
    $apiUrlWithPattern
);
$url = "http://$IPeedomus/api/set?action=periph.value";
$url .= "&api_user=$api_user";
$url .= "&api_secret=$api_secret";
$url .= "&periph_id=$periph_hebdom3";
$url .= "&value=$consohebdom3";

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update kwh hebdo: [" . $result . "]";
} else {
    echo "update m3 hebdo ok<br/>";
}

//******************************************** Update conso mensuelle m3***********************************************
$monthlyConsumptionParam = array_merge($bind, array(
    'periphId' => $periph_mensuelm3,
    'value'    => $consomensuellem3,
));

$url = str_replace(
    array_keys($monthlyConsumptionParam),
    array_values($monthlyConsumptionParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update kwh mensuel: [" . $result . "]";
} else {
    echo "update m3 mensuel ok<br/>";
}

//******************************************** Update conso annuelle m3***********************************************
$yearlyConsumptionParam = array_merge($bind, array(
    'periphId' => $periph_annuelm3,
    'value'    => $consoannuellem3,
));

$url = str_replace(
    array_keys($yearlyConsumptionParam),
    array_values($yearlyConsumptionParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update kwh annuel: [" . $result . "]";
} else {
    echo "update m3 annuel ok<br/>";
}


//******************************************** Update bilan***********************************************
$balanceParam = array_merge($bind, array(
    'periphId' => $etatbilan,
    'value'    => $bilan,
));

$url = str_replace(
    array_keys($balanceParam),
    array_values($balanceParam),
    $apiUrlWithPattern
);

$result = file_get_contents($url);

if (!strpos($result, '"success": 1')) {
    echo "Une erreur est survenue sur l'update bilan: [" . $result . "]";
} else {
    echo "update bilan ok<br/>";
}
echo $consoj1;
echo $consoj2;
echo $bilan;
