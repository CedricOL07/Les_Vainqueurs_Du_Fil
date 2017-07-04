/* Commentaire sur ce fichier !!!!
* ATTENTION : le player_page.js est utile à afficher le player coté client.
* Le problème rencontré si on veut le supprimer ces le chemin myAjax.ajaxurl dans la fonction générer a playlist
*/
$(document).ready(function(){
    var index_bdd_precedent;
    var myPlaylist = new jPlayerPlaylist({
        //jPlayer: "#mon_canvas",
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
        wmode : "window",
        emulateHtml : true,
        //backgroundColor : "http://www.le-fil.com/wp-content/themes/lefil_com/apple-icon-152x152.png",

    });

    $('#affichage_playlist-page-client').click(function(){
    $('#jp-playlits-id-page-client').toggle('fast');
    });

    /** REQUETE AJAX WORDPRESS
*
*   le terme action:'mon_action' refere à la fonction qui est effectué quand la requete ajax se fait (ici on recupere les url,titre,artiste de la playlist)
*
*   le terme url: ajaxurl  est le chemin vers le fichier qui recupere les requete ajax
*
*
*
*/

function generer_la_playlist(){
  var tableau_donnees= new Array();
  var artiste;
  var artiste_album_annee_gener = new String();
  $.ajax({
    url: myAjax.ajaxurl,
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

/*
* Fonction : Permet d'actualiser le player à tout instant sans nécessecité d'actualisation de la page.
* Cette fonction générera la nouvelle vidéo de la playlist par defaut.
*/
jQuery("#player_video").bind(jQuery.jPlayer.event.ended, function (event)
{
  var artiste_album_annee_ajout = new String();

  myPlaylist.remove(0);// efface le premier clip de la playlist du player.

  $.ajax({
    url: myAjax.ajaxurl,
    data:{
      'action' : 'recuperer_nouvelle_video_player_page_principal'
    },
    dataType: 'JSON',
    success: function(data) {
      //console.log("data : "+ data);
        $.each(data.data, function(index, value) {
            titre= value.titre;
            artiste_album_annee_ajout =  value.artiste + " - " + value.album  + " - " +value.annee;


          //Permet de générer la nouvelle video.
            myPlaylist.add({
      				title:value.titre,
      				m4v:value.url,
      				artist: artiste_album_annee_ajout
      			});
		     });

         //console.log("artiste" +artiste_album_annee_ajout);
        console.log(titre);
    }
  });

});


});
