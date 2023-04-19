<?php


function get_all_data() {
    /* 
    ** Permet de récupérer toutes les données des 20 MCDO aux alentours
    ** RETURN : Array contenant les 20 MCDO
    */
    $curl = curl_init('https://mcdonaldsfrance.webgeoservices.com/api/stores/search/?authToken=AIzaSyAiX19QNdei5Ja7TA2ahlg3Wb-p6eAUNOc&center=6.128885%3A45.899235&db=prod&dist=50000&limit=20&nb=20&orderDir=desc');

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Permet de désactiver le certificat (Verif SSL)
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Permet de ne pas afficher les données recup à l'écran et les stocker dans $data à la place

    $data_all_mcdo = curl_exec($curl);   // Execute l'url et renvoie true --> GOOD       OR      false --> BAD donc problèmes avec URL

    // Si problèmes avec l'URL 
    if($data_all_mcdo === false) {
        var_dump(curl_error($curl));    // Afficher l'erreur
    } else {
        $data_all_mcdo = json_decode($data_all_mcdo, true);   // On met les données JSON dans un tableau associatif
    }


    curl_close($curl);     // On ferme l'URL

    return $data_all_mcdo;
}


function clean_all_data($data_all_mcdo) {
    /* 
    ** Permet de garder seulement les valeurs de chaque Mcdo que l'on vient de récupérer et que l'on a besoin
    ** RETURN : Array contenant les 20 MCDO et leurs valeurs utiles : $data_all_mcdo_clean[0] -> Le premier Mcdo de la liste
    */

    $i = 0 ;

    $infoClean_All_Mcdo = [];

    foreach($data_all_mcdo['poiList'] as $unMcDo) {
        $data_all_mcdo_clean[$i]["distance"] = round( $data_all_mcdo['poiList'][$i]['dist'] / 1000, 1) ;
        $data_all_mcdo_clean[$i]["city"] = $data_all_mcdo['poiList'][$i]['poi']['location']['city'];
        $data_all_mcdo_clean[$i]["adress"] = $data_all_mcdo['poiList'][$i]['poi']['location']['streetLabel'];
        $data_all_mcdo_clean[$i]["id"] = $data_all_mcdo['poiList'][$i]['poi']['id'];
        $data_all_mcdo_clean[$i]["location"]["latitude"] = $data_all_mcdo['poiList'][$i]['poi']['location']['coords']['lat'];
        $data_all_mcdo_clean[$i]["location"]["longitude"] = $data_all_mcdo['poiList'][$i]['poi']['location']['coords']['lon'];
    
        $i++;
    }
    
    return $data_all_mcdo_clean;

}

function clean_all_data_products($data_all_mcdo_clean) {
    /* 
    ** Permet de garder seulement les valeurs pour chaque Mcdo s'ils ont des McFlurry ou non 
    ** [
        {
            product: McFlurry,
            available: true
        }, 
        {
            product: McFlurry,
            available: false
        }, 
        ...
    ]
    ** RETURN : Array contenant les 20 MCDO et leurs valeurs des McFlurry : $data_all_mcdo_products_clean[0] -> Le premier Mcdo de la liste
    */


    $i = 0;

    $data_all_mcdo_products_clean = [];

    foreach($data_all_mcdo_clean as $mcdo) {

        /*---------------------------------------- On charge les données ----------------------------------------*/
        $curl = curl_init('https://ws.mcdonalds.fr/api/catalog/gomcdo?eatType=EAT_IN&responseGroups=RG.CATEGORY.PICTURES&responseGroups=RG.CATEGORY.POPINS&responseGroups=RG.PRODUCT.CAPPING&responseGroups=RG.PRODUCT.CHOICE_FINISHED_DETAILS&responseGroups=RG.PRODUCT.INGREDIENTS&responseGroups=RG.PRODUCT.PICTURES&responseGroups=RG.PRODUCT.POPINS&responseGroups=RG.PRODUCT.RESTAURANT_STATUS&responseGroups=RG.PROMOTION.POPINS&restaurantRef='.$data_all_mcdo_clean[$i]['id']);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // Permet de désactiver le certificat (Verif SSL)
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Permet de ne pas afficher les données recup à l'écran et les stocker dans $data à la place

        $data_all_mcdo_products_dict = curl_exec($curl);   // Execute l'url et renvoie true --> GOOD       OR      false --> BAD donc problèmes avec URL

        // Si problèmes avec l'URL 
        if($data_all_mcdo_products_dict === false) {
            var_dump(curl_error($curl));    // Afficher l'erreur
        } else {
            $data_all_mcdo_products_dict = json_decode($data_all_mcdo_products_dict, true);   // On met les données JSON dans un tableau associatif
        }


        curl_close($curl);     // On ferme l'URL
        /*---------------------------------------- FIN On charge les données ----------------------------------------*/

        
        // echo "----------------------------------------  MCDO n°". $data_all_mcdo_clean[$i]['id'];
        // echo "<br>";

        /*---------------------------------------- Récupérer l'id pour avoir la ref 15 (correspondant à "NOS DESSERTS") */
        foreach($data_all_mcdo_products_dict['children'] as $key_mcdo_products => $mcdo_products) {
            if ($mcdo_products['ref'] == 15) {
                // echo $data_all_mcdo_products_dict['children'][$key_mcdo_products]['title'];
                // echo "<br>";
                $id_nos_desserts = $key_mcdo_products;
            }

        }


        /*---------------------------------------- Récupérer l'id pour avoir la ref "DESSERTS_GLACES" */
        foreach($data_all_mcdo_products_dict['children'][$id_nos_desserts]['children'] as $key_mcdo_products_desserts => $mcdo_products_desserts) {
            if ($mcdo_products_desserts['ref'] == 'DESSERTS_GLACES') {
                $id_desserts_glaces = $key_mcdo_products_desserts;
            }
        }




        // echo $data_all_mcdo_products_dict['children'][$id_nos_desserts]['children'][$id_desserts_glaces]['products'][0]['designation'];
        // echo $data_all_mcdo_products_dict['children'][$id_nos_desserts]['children'][$id_desserts_glaces]['products'][0]['available'];
        // echo "<br>";
        // echo "<br>";
        // echo "<br>";


        /*---------------------------------------- Ajouter les data à la list */
        $data_all_mcdo_products_clean[$i]['product'] = $data_all_mcdo_products_dict['children'][$id_nos_desserts]['children'][$id_desserts_glaces]['products'][0]['designation'] ;
        $data_all_mcdo_products_clean[$i]['available'] = $data_all_mcdo_products_dict['children'][$id_nos_desserts]['children'][$id_desserts_glaces]['products'][0]['available'] ;


        $i++;
    }

    return $data_all_mcdo_products_clean;

}



function save_to_json($data_all_mcdo_clean, $data_all_mcdo_products_clean) {

    $i = 0;

    $all_clean_info = [];

    foreach($data_all_mcdo_clean as $unMcDo) {
        $all_clean_info[$i]['distance'] = $data_all_mcdo_clean[$i]['distance'];
        $all_clean_info[$i]['city'] = $data_all_mcdo_clean[$i]['city'];
        $all_clean_info[$i]['adress'] = $data_all_mcdo_clean[$i]['adress'];
        $all_clean_info[$i]['id'] = $data_all_mcdo_clean[$i]['id'];
        $all_clean_info[$i]['location']['latitude'] = $data_all_mcdo_clean[$i]["location"]["latitude"];
        $all_clean_info[$i]['location']['longitude'] = $data_all_mcdo_clean[$i]["location"]["longitude"];
    
        $all_clean_info[$i]['product'] = $data_all_mcdo_products_clean[$i]['product'];
        $all_clean_info[$i]['available'] = $data_all_mcdo_products_clean[$i]['available'];
    

        $i++;
    }


    $file = fopen("mcdoUnvailable.js", "w") or die("Unable to open file!");
    $info_all_mcdo_clean_JSON = json_encode($all_clean_info, JSON_PRETTY_PRINT);
    fwrite($file, "export const lesMcDo = ".$info_all_mcdo_clean_JSON);
    fclose($file);

}






$data_all_mcdo = get_all_data();
$data_all_mcdo_clean = clean_all_data($data_all_mcdo);
$data_all_mcdo_products_clean = clean_all_data_products($data_all_mcdo_clean);
save_to_json($data_all_mcdo_clean, $data_all_mcdo_products_clean);


?>