/* Fonction de ce fichier :
*Il permet de gérer les interéaction avec le player coté product owner.
*Tous les contrôles du player de jplayer et l'implémentation des clips dans la  playlist du Player se font ici.
* Tous les affichages et l'aspect visuel se font dans le fichier index.php dans le dossier page_principal
*/

$(document).ready(function(){
  var index_bdd_precedent;
  var myPlaylist = new jPlayerPlaylist({
    jPlayer: "#player_video",
    cssSelectorAncestor: "#container_jplayer"
  }, [

  ],  {
    playlistOptions: {
      enableRemoveControls: false,
      autoPlay: true,
      keyEnabled: true,
    },
    //swfPath: "../../dist/jplayer",
    supplied: "webmv, ogv, m4v, oga, mp3",
    useStateClassSkin: true,
    autoBlur: false,
    smoothPlayBar: true,
    keyEnabled: true,
    audioFullScreen: true,

  });

  $('#affichage_playlist_homepage').click(function(){
  $('#jp-playlits-id-homepage').toggle('fast');
  });

/*
*       Generer des playlists
*
*  Requête Ajax pour générer des playlists à volonté.
*/


/** REQUETE AJAX WORDPRESS
*
*   le terme action:'mon_action' refere à la fonction qui est effectué quand la requete ajax se fait (ici on recupere les url,titre,artiste de la playlist)
*
*   le terme url: ajaxurl  est le chemin vers le fichier qui recupere les requete ajax
*
*
*/


function generer_la_playlist(){
  var tableau_donnees= new Array();
  var artiste;
  var artiste_album_annee_gener = new String();
  $.ajax({
    url: ajaxurl,
    data:{
      'action':'recuperer_videos_player_page_principale',
    },
    dataType: 'JSON',
    success: function(data) {
      //console.log(data);
      $.each(data.data, function(index, value) {
        //On va récupérer le nom de l'artiste pour chaque titre
        artiste_album_annee_gener=  value.artiste + " - " + value.album  + " - " +value.annee;
        var title=value.titre;

        myPlaylist.add({
    			title:value.titre,
    			m4v:value.url,
    			artist:artiste_album_annee_gener
        });
        //console.log(value.url);
        myPlaylist.play();// permet de s'affranchir du bouton play lors du chargmenent de la page.
        console.log(title);
      });

    },
    error: function (xhr, ajaxOptions, thrownError) {
      console.log(xhr.status);
      console.log(thrownError);
    }
  });
}
generer_la_playlist();



/*--------------------------------------- Règles internes --------------------------------------------------*/

var bool2=false;
var bool3=false;
var videonepasrepasser;
var artisteanepasrepasser;

//Fonction pour effacer les morceaux au fur et à mesure
jQuery("#player_video").bind(jQuery.jPlayer.event.ended, function (event)
{
	var current = myPlaylist.current;
	var playlist = myPlaylist.playlist;

  myPlaylist.remove(current-1);
	//On efface le morceau de la base de donnée également
	var titre_previous_current_track=myPlaylist.playlist[myPlaylist.current-1].title;// le -1 permet de récupérer la vidéo précédente.
  $.post(
		ajaxurl,
		{
			'action': 'effacer_et_ajouter_video_dans_table_playlist_par_defaut_webtv_plugin',
			'videocourante': titre_previous_current_track
		},
		function(response){
			console.log("video à ete ajouté : " + response);// pour mettre la réponse il faut aller mettre un echo dans la fonction correspondante dans l'action
		}
	);


});


/*
* Fonction : Permet d'actualiser le player à tout instant sans nécessecité d'actualisation de la page.
* Cette fonction générera la nouvelle vidéo de la playlist par defaut.
*/
jQuery("#player_video").bind(jQuery.jPlayer.event.ended, function (event)
{
  var artiste_album_annee_ajout = new String();
  $.ajax({
    url: ajaxurl,
    data:{
      'action' : 'recuperer_nouvelle_video_player_page_principal'
    },
    dataType: 'JSON',
    success: function(data) {
      //console.log("data : "+ data);
        $.each(data.data, function(index, value) {
            titre= value.titre;
            artiste_album_annee_ajout =  value.artiste + " - " + value.album  + " - " +value.annee;

            // + " annee : " + value.annee + "album : " value.album;

          //Permet de générer la nouvelle video.
            myPlaylist.add({
      				title:value.titre,
      				m4v:value.url,
      				artist: artiste_album_annee_ajout
      			});
		     });
         console.log("artiste" +artiste_album_annee_ajout);
        console.log(titre);
    }
  });

});


/*
* Fonction : Permet d'afficher la durée du clip en cours de lecture
*/
jQuery("#player_video").bind(jQuery.jPlayer.event.play, function (event)
{

	var current     = myPlaylist.current,
	playlist        = myPlaylist.playlist;

	var nom_clip_courant = playlist[current].title,
	artiste_clip_courant = playlist[current].artist,
	url_clip_courant = playlist[current].m4v;

	$.post(
		ajaxurl,
		{
			'action': 'recuperer_duree_clip',
			'nom_clip': nom_clip_courant,
			'url_clip': url_clip_courant
		},
		function(response){
			//console.log("Vidéo : " + artiste_clip_courant + " - " + nom_clip_courant + "\nDurée : " + response);
		}
	);


});



// Ne pas repasser le meme morceaux + meme artiste
/*
jQuery("#player_video").bind(jQuery.jPlayer.event.timeupdate, function (event){


  var current         = myPlaylist.current,
  playlist        = myPlaylist.playlist;
titre_webtvunction (index, obj){

    if (obj.title==event.jPlayer.status.media.title && index<current+19 && index!=current && bool2 ==false && index !=0 ){
      bool2=true;
      videonepasrepasser=index;
    }
  });
  if(bool2==true ){
    bool2=false;
    // var s=videonepasrepasser-current;
    //console.log('video a ne pas repasser en position '+videonepasrepasser+' soit dans  '+s+' vidéos');
    myPlaylist.remove(videonepasrepasser);
  }

  else{


    if(playlist.length>10){
      var art;
      var titr;
      var lien;
      jQuery.each(playlist, function (index, obj){
        if (obj.artist==event.jPlayer.status.media.artist && index<current+4 && index!=current && index !=0 && index<playlist.length-5 ){

          artisteanepasrepasser=index;
          bool3=true;
          art=obj.artist;
          titr=obj.title;
          lien=obj.m4v;
        }
      });
      if(bool3==true){
        // console.log('Artiste a ne pas repasser au '+artisteanepasrepasser+'  ');
        bool3=false;
        myPlaylist.remove(artisteanepasrepasser);
        myPlaylist.add({
          title:titr,
          artist:art,
          m4v:lien,
          loop:true

        });

      }
    }
  }
*/
/*-------------------------------------- FIN Règles internes ---------------------------------------------*/
/* REGLAGES DU LIVE */
  var on_live=false;
  $("#player_video").bind(jQuery.jPlayer.event.ended , function (event){
    //console.log(on_live);
    if(on_live==true ){
      myPlaylist.remove();
      myPlaylist.setPlaylist([{
        title:"LIVE",
        artist:"LE FIL",
        m4v:"http://localhost/wordpress/wp-content/plugins/admin_webtv_plugin/mp4/liveTest.mp4"
      }]);
      myPlaylist.option("autoPlay",true);
      myPlaylist.play();
    }
  });


  $('#live_btn').click(function(){
    if(on_live==false){
      on_live=true;
      $(this).html("Arreter le LIVE");
      //$('#player_video').prop('title', 'live_on');
      $.post(ajaxurl,{
        'action' : 'etat_live',
        'data' : on_live
      },function(response){
        //console.log(response);
      })
    }
    else if(on_live=true){
      on_live=false;
      myPlaylist.pause();
      myPlaylist.remove();
      generer_la_playlist();
      $(this).html("Lancer le LIVE");
      //$('#player_video').prop('title', 'live_off');
      $.post(ajaxurl,{
        'action' : 'etat_live',
        'data' : on_live
      },function(response){
       // console.log(response);
      })
    }
  });

});
