// CHANGEMENT D'ONGLET

	var onglet1=document.getElementById('onglet1');
	var onglet2=document.getElementById('onglet2');
	var onglet3=document.getElementById('onglet3');
	var onglet4=document.getElementById('onglet4');
	var onglet5=document.getElementById('onglet5');
	var onglet6=document.getElementById('onglet6');
	var blocOnglet1=document.getElementById('blocOnglet1');
	var blocOnglet2=document.getElementById('blocOnglet2');
	var blocOnglet3=document.getElementById('blocOnglet3');
	var blocOnglet4=document.getElementById('blocOnglet4');
	var blocOnglet5=document.getElementById('blocOnglet5');
	var blocOnglet6=document.getElementById('blocOnglet6');

	function griseOnglets(tabOnglets, tabBlocs) {
		for (let i=0; i<tabOnglets.length; i++) {
			if (tabBlocs[i]) {
				tabBlocs[i].style.display='none';
				tabOnglets[i].style.background='grey';
			}
		}
	}

	if (onglet1) {
	onglet1.addEventListener('click', function(e) {
		blocOnglet1.style.display='';
		blocOnglet1.style.background='#BEBEBE';
		onglet1.style.background='#BEBEBE';
		griseOnglets([onglet2, onglet3, onglet4, onglet5, onglet6], [blocOnglet2, blocOnglet3, blocOnglet4, blocOnglet5, blocOnglet6]);	
	},false);
	}
	
	if (onglet2) {
	onglet2.addEventListener('click', function(e) {
		blocOnglet1.style.display='none';
		onglet1.style.background='grey';
		blocOnglet2.style.display='';
		onglet2.style.background='#BEBEBE';
		blocOnglet2.style.background='#BEBEBE';
		griseOnglets([onglet1, onglet3, onglet4, onglet5, onglet6], [blocOnglet1, blocOnglet3, blocOnglet4, blocOnglet5, blocOnglet6]);		
	},false);
	}

	if (onglet3) {
	onglet3.addEventListener('click', function(e) {
		blocOnglet3.style.background='#BEBEBE';
		blocOnglet3.style.display='';
		onglet3.style.background='#BEBEBE';
		griseOnglets([onglet1, onglet2, onglet4, onglet5, onglet6], [blocOnglet1, blocOnglet2, blocOnglet4, blocOnglet5, blocOnglet6]);
	},false);
	}

	if (onglet4) {
		onglet4.addEventListener('click', function(e) {
			blocOnglet4.style.background='#BEBEBE';
			blocOnglet4.style.display='';
			onglet4.style.background='#BEBEBE';
			griseOnglets([onglet1, onglet2, onglet3, onglet5, onglet6], [blocOnglet1, blocOnglet2, blocOnglet3, blocOnglet5, blocOnglet6]);		
		},false);
	}
	
	if (onglet5) {
		onglet5.addEventListener('click', function(e) {
			blocOnglet5.style.background='#BEBEBE';
			blocOnglet5.style.display='';
			onglet5.style.background='#BEBEBE';
			griseOnglets([onglet1, onglet2, onglet3, onglet4, onglet6], [blocOnglet1, blocOnglet2, blocOnglet3, blocOnglet4, blocOnglet6]);
		},false);
	}

	if (onglet6) {
		onglet6.addEventListener('click', function(e) {
			blocOnglet6.style.background='#BEBEBE';
			blocOnglet6.style.display='';
			onglet6.style.background='#BEBEBE';
			griseOnglets([onglet1, onglet2, onglet3, onglet4, onglet5], [blocOnglet1, blocOnglet2, blocOnglet3, blocOnglet4, blocOnglet5]);		
		},false);
	}