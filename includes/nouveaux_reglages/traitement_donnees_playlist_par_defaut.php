<?php


/**************************************************************************************************************************
**
**
**        Fichier contenant les fonctions exécutées par les différentes reqûetes ajax de la page NOUVEAUX REGLAGES
**
**
*******************************************************************************************************************************/
/*
Appel des différentes fonctions du programme
*/

add_action('wp_ajax_recuperer_programmation','recuperer_programmation');
add_action('wp_ajax_recuperer_noms_reglages','recuperer_noms_reglages');
add_action('wp_ajax_recup_genre_video_courante_logo', 'recup_genre_video_courante_logo');
add_action('wp_ajax_enregistrer_reglage_par_defaut','enregistrer_reglage_par_defaut');
add_action('wp_ajax_recuperer_nouvelle_video_player_page_principal', 'recuperer_nouvelle_video_player_page_principal');
add_action('wp_ajax_recuperer_videos_player_page_principale_par_defaut', 'recuperer_videos_player_page_principale_par_defaut' );
add_action('wp_ajax_supprimer_logo_de_playlist_par_defaut', 'supprimer_logo_de_playlist_par_defaut');
add_action('wp_ajax_verif_et_effacer_video_courante_avant_passage_logo','verif_et_effacer_video_courante_avant_passage_logo');
add_action('pluginwebtv_freq_logo', 'freq_logo');
add_action('pluginwebtv_insertion_logo_dans_playlist_par_defaut', 'insertion_logo_dans_playlist_par_defaut',1,3);
add_action('pluginwebtv_generer_la_playlist_par_defaut', 'generer_la_playlist_par_defaut');
add_action('wp_ajax_recup_freq_logo','recup_freq_logo');
add_action('wp_ajax_insertion_logo','insertion_logo');
add_action('wp_ajax_recup_id_video_courante','recup_id_video_courante');


function recuperer_id_playlist_par_defaut(){
      $query = "SELECT ParDefaut FROM" . $wpdb->prefix . "playlistenregistrees_webtv_plugin;";
      $result=$wpdb->get_results($query);

}


/*
Cette focntion initialise les variable créer $par_defaut,$nom_reglage,...
par les instructions données par le client lors de la demande POST.
*/

function enregistrer_reglage_par_defaut(){

        global $wpdb;
        // Liste des variables transmises dans la requête ajax
        // On passe un booléen pour vérifier que la playlist a été définie comme par défaut ou non
        if(isset($_POST['pardefaut'])){
            $par_defaut=$_POST['pardefaut'];
            if ($par_defaut == true){
              $par_defaut =1;
            }
          }

        // = true si playlist définie comme par défaut
        // On passe un booléen pour vérifier que la playlist doit être passer directement à la suite ou non
        if(isset($_POST['nom_reglage'])){$nom_reglage=$_POST['nom_reglage'];}
        if(isset($_POST['pourcentage_poprock'])){$pourcentage_poprock=$_POST['pourcentage_poprock'];}
        if(isset($_POST['pourcentage_hiphop'])){$pourcentage_hiphop=$_POST['pourcentage_hiphop'];}
        if(isset($_POST['pourcentage_jazzblues'])){$pourcentage_jazzblues=$_POST['pourcentage_jazzblues'];}
        if(isset($_POST['pourcentage_musiquemonde'])){$pourcentage_musique_monde=$_POST['pourcentage_musiquemonde'];}
        if(isset($_POST['pourcentage_hardrock'])){$pourcentage_hardrock=$_POST['pourcentage_hardrock'];}
        if(isset($_POST['pourcentage_electro'])){$pourcentage_electro=$_POST['pourcentage_electro'];}
        if(isset($_POST['pourcentage_chanson'])){$pourcentage_chanson=$_POST['pourcentage_chanson'];}
        if(isset($_POST['pourcentage_autres'])){$pourcentage_autres=$_POST['pourcentage_autres'];}
        if(isset($_POST['annee_max'])){$annee_max=$_POST['annee_max'];}
        if(isset($_POST['annee_min'])){$annee_min=$_POST['annee_min'];}
        if(isset($_POST['qualite_min'])){$qualite_min = $_POST['qualite_min'];}
        if(isset($_POST['freq_logo'])){$freq_logo = $_POST['freq_logo'];}

        if ($par_defaut == 1){

          $effacer_ancienne_playlist_par_defaut="DELETE FROM " . $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut='$par_defaut';";
          $select1=$wpdb->query($effacer_ancienne_playlist_par_defaut);

          $inserer_nouvelle_playlist_par_defaut="INSERT INTO " . $wpdb->prefix . "playlistenregistrees_webtv_plugin(nom,pourcentage_poprock,pourcentage_rap,pourcentage_jazzblues,pourcentage_musiquemonde,
          pourcentage_hardrock,pourcentage_electro,pourcentage_chanson,pourcentage_autres,annee_max,annee_min,qualite_min,Freq_logo,ParDefaut) VALUES('$nom_reglage','$pourcentage_poprock','$pourcentage_hiphop','$pourcentage_jazzblues','$pourcentage_musique_monde','$pourcentage_hardrock','$pourcentage_electro','$pourcentage_chanson','$pourcentage_autres','$annee_max','$annee_min','$qualite_min','$freq_logo','$par_defaut');";

          $select = $wpdb->query($inserer_nouvelle_playlist_par_defaut);

        }

        do_action('pluginwebtv_generer_la_playlist_par_defaut');

    }

/*
*Fonction : permet de récupérer les noms de la playlist enregistrer  : utile dans le fihier nouveaux_reglages.js
*
*/
function recuperer_noms_reglages(){
    global $wpdb;
    $recuperer_noms="SELECT nom FROM " . $wpdb->prefix . "playlistenregistrees_webtv_plugin;";
    $resut=$wpdb->get_results($recuperer_noms);
    wp_send_json_success($resut);

}

function generer_la_playlist_par_defaut(){

    global $wpdb;
    global $tab_url;
    global $tab_titres;
    global $tab_genres;
    global $tab_artistes;
    global $tab_annees;
    global $tab_album;
    global $tab_logo_titre;
    global $tab_logo_url;

    //On chope les playlists enregistrés, on tri par date et quand creneau libre on met playlist defaut
    $ldefaut=1;
    //$querydefaut="SELECT * FROM " . $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut='$ldefaut';";
    $querydefaut="SELECT * FROM " . $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut = 1;";
    $resultdefaut=$wpdb->get_results($querydefaut);
    foreach($resultdefaut as $resdefaut){
        $nomdefaut =$resdefaut->nom;
        //NULL si case vide
        $artistehightdefaut=$resdefaut->artiste_highlight;
        $pubsinternesdefaut=$resdefaut->publicites_internes;
        $pubsexternesdefaut=$resdefaut->publicites_externes;
        $poprockdefaut=$resdefaut->pourcentage_poprock;
        $hiphopdefaut=$resdefaut->pourcentage_rap;
        $jazzbluesdefaut=$resdefaut->pourcentage_jazzblues;
        $musiquemondedefaut=$resdefaut->pourcentage_musiquemonde;
        $electrodefaut=$resdefaut->pourcentage_electro;
        $hardrockdefaut=$resdefaut->pourcentage_hardrock;
        $chansondefaut=$resdefaut->pourcentage_chanson;
        $autresdefaut=$resdefaut->pourcentage_autres;
        $annee_max=$resdefaut->annee_max;
        $annee_min=$resdefaut->annee_min;
        $qualite_min=$resdefaut->qualite_min;
        $frequence_logo = $resdefaut->Freq_logo;

            do_action('pluginwebtv_generer_playlist_par_defaut',$poprockdefaut,$hiphopdefaut,$jazzbluesdefaut,$musiquemondedefaut,$hardrockdefaut,$electrodefaut,$chansondefaut,$autresdefaut,$annee_max,$annee_min,$qualite_min);
            // appelle la fonction de récupération des logo.


    }

    $effacer_existant ="TRUNCATE TABLE " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin;";
    $wpdb->query($effacer_existant);

    //On met tout ca dans la table Playlist
    $titre = str_replace("'","''",$tab_titres);
    $artistes = str_replace("'","''",$tab_artistes);
    $genres = str_replace("'","''",$tab_genres);
    $annees = str_replace("'","''",$tab_annees);
    $album = str_replace("'","''",$tab_album);
    $logo_titre = str_replace("'","''",$tab_logo_titre);

        for($k=0;$k<12;$k++){ // remettre sizeof($titre) une fois pb résolu.

            $inserer="INSERT INTO " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin(titre,url,artiste,genre,annee,album) VALUES('$titre[$k]','$tab_url[$k]','$artistes[$k]','$genres[$k]','$annees[$k]', '$album[$k]')";
            $wpdb->query($inserer);
          }

/*
* Fonction permettant d'afficher la programmation (situé dans le fihier homepage.js)
*
*/

  function recuperer_programmation(){
  // permet de récupérer le nom, le début et la fin d'une playlist enregistrée dans la base de donnée

      global $wpdb;
      $query="SELECT nom,Debut,Fin FROM " . $wpdb->prefix . "playlistenregistrees_webtv_plugin;";
      $result=$wpdb->get_results($query);
      wp_send_json_success($result);
  }


}
/*
* fonction  : permet de savoir si la video courante possède un genre logo
*/

function recup_bool_video_courante_logo(){
  global $wpdb;
  if(isset($_POST['videocourante'])){$videocourante = $_POST['videocourante'];}

  $query_recup_logo_bdd = "SELECT genre FROM ". $wpdb->prefix ."videos_logo_webtv_plugin WHERE titre'$videocourante' LIMIT 1;";
  $reponse_recup_logo_bdd = $wpdb->get_var($query_recup_logo_bdd);
  if ($reponse_recup_logo_bdd == "Logo")
  {
    echo(1);
  }
  else {
    echo (0);
  }
}
/*
* Fonction récupérer le titre et l'url du logo
*
*/
function insertion_logo(){
  global $wpdb;
  $query_recup_logo_bdd = "SELECT titre,url FROM ". $wpdb->prefix ."videos_logo_webtv_plugin ORDER BY RAND() LIMIT 1;";
  $reponse_recup_logo_bdd = $wpdb->get_results($query_recup_logo_bdd);
  wp_send_json_success($reponse_recup_logo_bdd);
}

/*
* Fonction recupère la fréquence logo
*
*/
function recup_freq_logo(){
  global $wpdb;
  $query_recup_freq_logo_bdd = "SELECT Freq_logo FROM ". $wpdb->prefix ."playlistenregistrees_webtv_plugin WHERE ParDefaut=1 LIMIT 1;";
  $reponse_recup_freq_logo_bdd = $wpdb->get_var($query_recup_freq_logo_bdd);
  echo($reponse_recup_freq_logo_bdd);

}
/*
* Fonction recupère l'id de la video courante
*
*/
function recup_id_video_courante(){
  global $wpdb;
  if(isset($_POST['videocourante'])){$videocourante = $_POST['videocourante'];}

  $query_recup_id_video_courante = "SELECT id FROM ". $wpdb->prefix ."playlist_par_defaut_webtv_plugin WHERE titre='$videocourante' LIMIT 1;";
  $reponse_query_recup_id_video_courante = $wpdb->get_var($query_recup_id_video_courante);
  echo ($reponse_query_recup_id_video_courante);
}




/*
* fonction : Création des tableaux $tab_logo_titre et $tab_logo_url
*  les doublons sont acceptés.
*/
function freq_logo($frequence_logo){
  global $wpdb;
  global $tab_video_logo;
  global $tab_logo_titre;
  global $tab_logo_url;
  global $tab_logo_genre;

  $query_id_video_logo="SELECT video_id FROM " . $wpdb->prefix . "relation_webtv_plugin WHERE genre_id='13' ORDER BY RAND();";// genre du Logo est 13
  $result_id_video_logo = $wpdb->get_results($query_id_video_logo);
  foreach ($result_id_video_logo as   $results) {
    $tab_video_logo_verif[] = $results->video_id;
  }
  //si il n'y a pas de logo dans la table relation_webtv_plugin
 if (sizeof($tab_video_logo_verif) > 0){

   //incrémente un logo tant que la fréquence choisi par l'utilisateur n'est pas atteinte.
   while (sizeof($tab_video_logo) <= ($frequence_logo-1)) {// -1 car le tableau commence à 0

     // insère les logo présent dans le tableau  $tab_video_logo_verif issue de la requète
     for ($i=0; $i < sizeof($tab_video_logo_verif) ; $i++) {

       // si  le tableau de logo final  dépasse la fréquence on stop la boucle sinon on continue de répèter meme s'il y a présence de doublons.
        if (sizeof($tab_video_logo) > ($frequence_logo -1) ) {// -1 car le tableau commence à 0
          break;
        }
        else{
          $tab_video_logo[] = $tab_video_logo_verif[$i];

        }
      }
    }
    // récupère titre et url du tableau des id_ logo créer
    foreach ($tab_video_logo as $key) {
      $query_titre_url_video_logo="SELECT titre,url FROM " . $wpdb->prefix . "videos_webtv_plugin WHERE id='$key';";
      $tab_logo_titre[] =$wpdb->get_var($query_titre_url_video_logo,0,0);
      $tab_logo_url[] = $wpdb->get_var($query_titre_url_video_logo,1,0);

      $query_genre_id_video_logo="SELECT genre_id FROM " . $wpdb->prefix . "relation_webtv_plugin WHERE video_id='$key';";
      $reponse_genre_id_video_logo = $wpdb->get_var($query_genre_id_video_logo,0,0);

      $query_genre_video_logo="SELECT Genre FROM " . $wpdb->prefix . "genre_webtv_plugin WHERE id='$reponse_genre_id_video_logo';";
      $tab_logo_genre[] = $wpdb->get_var($query_genre_video_logo,0,0);

    }

  }
 else {
   echo '<script>alert(\"Il n\'y a pas de logo dans la base de données donc aucun logo n\'est insérer dans la playlist. \")</script>';

 }
}




/*
* Fonction : permet de générer le nombre de clips à générer dans la table playlist_par_defaut_webtv_plugin en fonction du nombre de logo(s)
*/
function insertion_logo_dans_playlist_par_defaut($frequence_logo, $id_video_courante, $titre_video_courante){
  global $wpdb;
  global $tab_logo_titre;
  global $tab_logo_url;
  global $tab_logo_genre;
  global $titre_nouvelle_video;
  $random = rand (0 , ($frequence_logo-1));// ce nombre permet de choisir un logo au hasard selon les logos définies dans le tableautab_logo_url

  if ($frequence_logo != 0){

      // si le reste de la division entre l'id et la frequence du logo est égale à 0 alors on ajoute une pub à la suite.
    if ($id_video_courante % $frequence_logo == 0 && $random >= 0) {

      //récupère l'id de la video.
      $query_id_video_courante = "SELECT id FROM ". $wpdb->prefix . "videos_webtv_plugin WHERE titre='$titre_video_courante' LIMIT 1;";
      $reponse_id_video_courante = $wpdb -> get_var($query_id_video_courante);


      //recupère les dates et la qualité demandé pour la par defaut avant d'ajouter une nouvelle video.
      $query_annee_max = "SELECT annee_max FROM ". $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut=1 LIMIT 1;";
      $reponse_annee_max = $wpdb->get_var($query_annee_max);

      $query_annee_min = "SELECT annee_min FROM ". $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut=1 LIMIT 1;";
      $reponse_annee_min = $wpdb->get_var($query_annee_min);

      $query_qualite_min = "SELECT qualite_min FROM ". $wpdb->prefix . "playlistenregistrees_webtv_plugin WHERE ParDefaut=1 LIMIT 1;";
      $reponse_qualite_min = $wpdb->get_var($query_qualite_min);


      //Récupération de l'id du genre de la vidéo courante
      $query_id_genre_video_courante = "SELECT genre_id FROM ". $wpdb->prefix . "relation_webtv_plugin WHERE video_id='$reponse_id_video_courante' LIMIT 1;";
      $reponse_id_genre_video_courante = $wpdb -> get_var($query_id_genre_video_courante);

              //-------Video à ajouter ------//
      do_action('pluginwebtv_nouvelle_video_comparaison',$reponse_id_genre_video_courante, $titre_video_courante, $reponse_annee_min, $reponse_annee_max, $reponse_qualite_min );



      //Récupère information url et id de la nouvelle video
       $query_id_video_a_ajouter_meme_genre = "SELECT id,url FROM ". $wpdb->prefix . "videos_webtv_plugin WHERE titre='$titre_nouvelle_video' LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
       $reponse_id_video_a_ajouter_meme_genre = $wpdb -> get_var($query_id_video_a_ajouter_meme_genre, 0);
       $reponse_url_video_a_ajouter_meme_genre = $wpdb -> get_var($query_id_video_a_ajouter_meme_genre, 1);


       //Récupération des informations liées à la nouvelle video avec le même genre que la vidéo courante.
        $query_ids_video_a_ajouter_meme_genre = "SELECT artiste_id,genre_id, album_id, annee_id FROM ". $wpdb->prefix . "relation_webtv_plugin WHERE video_id='$reponse_id_video_a_ajouter_meme_genre'   LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
        $reponse_ids_video_a_ajouter_meme_genre = $wpdb -> get_results($query_ids_video_a_ajouter_meme_genre);

        foreach ( $reponse_ids_video_a_ajouter_meme_genre  as $result){
          $id_genre_video_a_ajouter_meme_genre = $result->genre_id;
          $id_artiste_video_a_ajouter_meme_genre = $result->artiste_id;
          $id_album_video_a_ajouter_meme_genre = $result->album_id;
          $id_annee_video_a_ajouter_meme_genre = $result->annee_id;


          $query_genre_video_a_ajouter_meme_genre = "SELECT Genre FROM ". $wpdb->prefix . "genre_webtv_plugin WHERE id='$id_genre_video_a_ajouter_meme_genre' LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
          $reponse_genre_video_a_ajouter_meme_genre = $wpdb -> get_var($query_genre_video_a_ajouter_meme_genre);

          $query_artiste_video_a_ajouter_meme_genre = "SELECT nom FROM ". $wpdb->prefix . "artiste_webtv_plugin WHERE id='$id_artiste_video_a_ajouter_meme_genre' LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
          $reponse_artiste_video_a_ajouter_meme_genre = $wpdb -> get_var($query_artiste_video_a_ajouter_meme_genre);

          $query_annee_video_a_ajouter_meme_genre = "SELECT annee FROM ". $wpdb->prefix . "annee_webtv_plugin WHERE id='$id_annee_video_a_ajouter_meme_genre' LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
          $reponse_annee_video_a_ajouter_meme_genre = $wpdb -> get_var($query_annee_video_a_ajouter_meme_genre);

          $query_album_video_a_ajouter_meme_genre = "SELECT album FROM ". $wpdb->prefix . "album_webtv_plugin WHERE id='$id_album_video_a_ajouter_meme_genre' LIMIT 1;"; // order by rand permet de lister aléatoirement les clips musiquaux
          $reponse_album_video_a_ajouter_meme_genre = $wpdb -> get_var($query_album_video_a_ajouter_meme_genre);
        }


        //Mise à jour de la table playlist_par_defaut_webtv_plugin avec un clips video du même genre que la video courante supprimé
        $query_titre_url_genres_artistes_annee_album_video_a_ajouter_meme_genre_dans_table_playlist_par_defaut_webtv_plugin = "INSERT INTO " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin(titre,url,artiste,genre, annee, album) VALUES('$titre_nouvelle_video','$reponse_url_video_a_ajouter_meme_genre','$reponse_artiste_video_a_ajouter_meme_genre','$reponse_genre_video_a_ajouter_meme_genre','$reponse_annee_video_a_ajouter_meme_genre','$reponse_album_video_a_ajouter_meme_genre')";
        $wpdb -> query($query_titre_url_genres_artistes_annee_album_video_a_ajouter_meme_genre_dans_table_playlist_par_defaut_webtv_plugin);

        $query_select_min_id_de_video_courante = "SELECT MIN(id) FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE titre='$titre_video_courante' ";
        $reponse_select_min_id_de_video_courante = $wpdb->get_var($query_select_min_id_de_video_courante);

        //Requete qui supprime la video courante en fonction de son id de la playlist par defaut.
        $query_del_titre_video_courante="DELETE FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE id='$reponse_select_min_id_de_video_courante' ";
        $wpdb->query($query_del_titre_video_courante);

        $query_inserer_nouveau_logo="INSERT INTO " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin(titre,url,artiste,genre,annee,album) VALUES('$tab_logo_titre[$random]','$tab_logo_url[$random]','undef','$tab_logo_genre[$random]','undef', 'undef')";
        $wpdb->query($query_inserer_nouveau_logo);
        //Permet de trier la table par odre croissant !! Utile pour démarer toujours au clip en cours. SELECT n'actualise pas la BDD comme on pourrait le croire si on effectue
        // la requête dans phpmyadmin.
        $query_tri_asc = "ALTER TABLE " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin ORDER BY id ASC;";
        $wpdb->query($query_tri_asc);


      }
    }
    unset($titre_nouvelle_video);
    unset($tab_logo_titre);
    unset($tab_logo_url);
}


function supprimer_logo_de_playlist_par_defaut(){
  global $wpdb;
  $delete_logo = "DELETE FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE genre='Logo' ;";
  $wpdb->query($delete_logo);

}

/*
* Fonctions : utile pour le fichier js du player_homepage.js
*
*/

function recuperer_videos_player_page_principale_par_defaut() {
    global $wpdb;
    $query="SELECT titre, artiste, url, annee, album FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin;";// plus de limite la playlist par default tournera indéfiniment
    $result=$wpdb->get_results($query);
    wp_send_json_success($result);
}


/*
*Fonction : Permet de trouver le max id d'une video dans la table playlist par defaut.
*Très utile pour la fonction situé dans le player_homepage.js permettant d'ajouter
*lA dernier video ajouté dans la playlist.
*/
 function recuperer_nouvelle_video_player_page_principal(){
    global $wpdb;
    $max_id = 0;

    //Phase obligatoire pour connaitre l'id de la nouvelle video car celui ci est générer automatiquement lors de l'insertion
    $query_recup_id_nouvelle_video = "SELECT id FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin; ";
    $reponse_recup_id_nouvelle_video = $wpdb->get_results($query_recup_id_nouvelle_video);
    foreach ($reponse_recup_id_nouvelle_video as $key ) {
      if ($max_id <= $key->id){
        $max_id = $key->id;
      }else{
        $max_id = $max_id ;
      }
    }
    $query_recup_genre_nouvelle_video = "SELECT genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE id='$max_id' ; ";
    $reponse_recup_genre_nouvelle_video = $wpdb->get_var($query_recup_genre_nouvelle_video);

    if($reponse_recup_genre_nouvelle_video == "Logo"){

      $max_id_vid = $max_id -1;// récupère la video précédent le logo
      $query_recup_titre_url_nouvelle_video_et_logo = "SELECT titre, artiste, url, annee, album, genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE id IN ('$max_id','$max_id_vid');";
      $reponse_recup_titre_url_nouvelle_video_et_logo = $wpdb->get_results($query_recup_titre_url_nouvelle_video_et_logo);
      wp_send_json_success($reponse_recup_titre_url_nouvelle_video_et_logo);
    }
    else {
      $query_recup_titre_url_nouvelle_video_ou_logo = "SELECT titre, artiste, url, annee, album, genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE id='$max_id' ; ";
      $reponse_recup_titre_url_nouvelle_video_ou_logo = $wpdb->get_results($query_recup_titre_url_nouvelle_video_ou_logo);
      wp_send_json_success($reponse_recup_titre_url_nouvelle_video_ou_logo);
    }

}

/**
* Fonction : permet de savoir s'il y a un logo dans la playlist et retourne un nombre 1 s'il y en a ou 0 s'il y en a un pas
*/
function recup_genre_video_courante_logo(){
    global $wpdb;
    if(isset($_POST['videocourante'])){$videocourante = $_POST['videocourante'];}

    $query_recup_genre_video_courante = "SELECT  genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE titre='$videocourante'; ";

    $reponse_recup_genre_video_courante = $wpdb->get_var($query_recup_genre_video_courante);

    if ($reponse_recup_genre_video_courante == "Logo"){
      echo (1);
      return;
    }
    else{
      echo(0);
      return;
    }


}
/*
* Fonction : récupère le nom de la video avant de passer à la video du logo
* Utilité : permet de récupérer le titre de cette video pour récupérer son genre et pouvoir insérer une nouvelle video avec le meme genre, intervalle d'année
* et sa qualité min.
*/
function verif_et_effacer_video_courante_avant_passage_logo(){
    global $wpdb;
    global $titre_nouvelle_video;
    if(isset($_POST['videocourante_previous'])){$videocourante_previous = $_POST['videocourante_previous'];}
    if(isset($_POST['videocourante'])){$videocourante = $_POST['videocourante'];}

    $query_recup_genre_video= "SELECT  genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin; ";
    $reponse_recup_genre_video = $wpdb->get_results($query_recup_genre_video);

    $query_recup_genre_video_courante_previous = "SELECT  genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE titre='$videocourante_previous' ; ";
    $reponse_recup_genre_video_courante_previous = $wpdb->get_var($query_recup_genre_video_courante_previous);

    $query_recup_genre_video_courante = "SELECT  genre FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE titre='$videocourante' ; ";
    $reponse_recup_genre_video_courante = $wpdb->get_var($query_recup_genre_video_courante);
    echo($reponse_recup_genre_video_courante);

    foreach ($reponse_recup_genre_video as $key) {

      if ($key->genre == "Logo"  && $reponse_recup_genre_video_courante != "Logo" ){

          $tab_video_courante[] = $videocourante_previous;
          //récupère l'id de la video à supprimer
          $query_select_min_id_de_video_courante = "SELECT MIN(id) FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE titre='$tab_video_courante[0]' ";
          $reponse_select_min_id_de_video_courante = $wpdb->get_var($query_select_min_id_de_video_courante);

          $query_del_titre_video_courante="DELETE FROM " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin WHERE id='$reponse_select_min_id_de_video_courante' ";
          $wpdb->query($query_del_titre_video_courante);


          //Permet de trier la table par odre croissant !! Utile pour démarer toujours au clip en cours. SELECT n'actualise pas la BDD comme on pourrait le croire si on effectue
          // la requête dans phpmyadmin.
          $query_tri_asc = "ALTER TABLE " . $wpdb->prefix . "playlist_par_defaut_webtv_plugin ORDER BY id ASC;";
          $wpdb->query($query_tri_asc);

          break;// coupe la boucle
      }
    }
}



?>
