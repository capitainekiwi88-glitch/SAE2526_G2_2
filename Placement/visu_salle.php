<html>    
     <head>    
     <title></title>    
     <script type="text/javascript">    
         var url = 'http://www.google.com'; //the details page you want to display... 
     </script>    
     <style>    
         .loading-indicator {    
             font-size:8pt;    
             background-image:url(../images/loading/loading.gif);    
             background-repeat: no-repeat;      
             background-position:top left;    
             padding-left:20px;    
             height:18px;    
             text-align:left;    
         }    
         #loading{    
             position:absolute;    
             left:45%;    
             top:40%;    
             border:3px solid #B2D0F7;    
             background:white url(../images/loading/block-bg.gif) repeat-x;    
             padding:10px;    
             font:bold 14px verdana,tahoma,helvetica;    
             color:#003366;    
             width:180px;    
             text-align:center;    
         }    
     </style>    
     <div id="loading">    
         <div class="loading-indicator">    
             Page Loading...    
         </div>    
     </div>    
     </head>    
     <body onload="location.href = url;" style="overflow:hidden;overflow-y:hidden">    
     </body>    
     <script>    
        if(document.layers) {    
             document.write('<Layer src="' + url + '" visibility="hide"></Layer>');    
         }    
        else if(document.all || document.getElementById) {    
             document.write('<iframe src="' + url + '" style="visibility:hidden;"></iframe>');    
         }    
        else {    
             location.href = url;    
         }    
     </script>    
</html>



	<!-- ###################### Bouton retour ################### -->
	<a href="index.php?p=gest_salle"><img class="imgBack" src="images/back.png"></a>
	<style>
		.imgBack
		{
			width: 20px;
			height: 20px;
			float: left;
		}
	</style>