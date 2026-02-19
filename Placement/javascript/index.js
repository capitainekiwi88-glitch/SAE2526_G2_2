// ################## MENU DEROULANT GESTION ##################

	// Souris passe sur le menu
	$('#btngest').mouseenter(function() {
		// Animation (agrandis bloc)
		$('#blocgest').stop().animate({
	     	height: '247px'
	    },300);
	})
	
	// Souris sors du menu
	$('#blocgest').mouseleave(function() {
		// Animation (agrandis bloc)
		$('#blocgest').stop().animate({
	     	height: '0px'
	    },300);
	})
	
	

	// FERME TOUT LES MENUS EN SORTANT DE TOPBAR
	$('.topbar').mouseleave(function() {
		// Animation (agrandis bloc)
		$('#blocgest').stop().animate({
	     	height: '0px'
		},300);
	})