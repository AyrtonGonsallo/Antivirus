document.addEventListener("DOMContentLoaded", async () => {
	
/****** Equal (min-height) for textes,titles, exc in inline blocs *****/
        var max_heightTxt =(classes)=>{
            var max_height_txt = jQuery(classes).map(function (){return jQuery(this).height();}).get();
            minHeightTxt = Math.max.apply(null, max_height_txt);
            jQuery(classes).css( "min-height",minHeightTxt );
        }
   
	setTimeout(() => {
//         dupliquer le code suivant ou cas de besoin d'autres element de méme hauteur !
		max_heightTxt('.paragrph p');
	
}, 100);

	/*************************** footer lang switcher ****************************/





	
});

/***************************** fil d ariane - icon-home ***************************************/
document.addEventListener('DOMContentLoaded', function () {
  const IMAGE_CLASS = 'custom-breadcrumb-home-icon';
  const imageSrc = "/wp-content/uploads/2025/10/home-3.webp";// 🔹 remplace ici par le bon chemin

  function insertImageBeforeHome() {
    // Sélectionne le lien "Accueil" (ou le premier lien du fil d’Ariane)
    let homeLink = document.querySelector('#breadcrumbs a[href="https://test.antivirusedition.com/"]')
                || document.querySelector('#breadcrumbs a[href="/"]')
                || document.querySelector('#breadcrumbs a');

    if (!homeLink) return;

    // Évite les doublons
    const prev = homeLink.previousElementSibling;
    if (prev && prev.classList && prev.classList.contains(IMAGE_CLASS)) return;

    // Crée l’élément image
    const img = document.createElement('img');
    img.src = imageSrc;
    img.alt = "Accueil";
    img.className = IMAGE_CLASS;
    img.style.width = '16px';
    img.style.height = '16px';
    img.style.marginRight = '6px';
	img.style.marginTop='-6px';
    img.style.verticalAlign = 'middle';

    // Insère l’image avant le lien
    homeLink.parentNode.insertBefore(img, homeLink);
  }

  // Exécution initiale
  insertImageBeforeHome();

  // Observe les changements du DOM (#breadcrumbs peut être réécrit par Elementor)
  const breadcrumbs = document.querySelector('#breadcrumbs');
  if (breadcrumbs) {
    const observer = new MutationObserver(function () {
      insertImageBeforeHome();
    });
    observer.observe(breadcrumbs, { childList: true, subtree: true });
  } else {
    // Fallback si #breadcrumbs n’existe pas encore
    const bodyObserver = new MutationObserver(function (mutations, obs) {
      if (document.querySelector('#breadcrumbs')) {
        insertImageBeforeHome();
        const br = document.querySelector('#breadcrumbs');
        const observer = new MutationObserver(insertImageBeforeHome);
        observer.observe(br, { childList: true, subtree: true });
        obs.disconnect();
      }
    });
    bodyObserver.observe(document.body, { childList: true, subtree: true });
  }
});
/********************************* active entreprise **************************************/

jQuery(document).ready(function($) {
	var href2 = window.location.href; // supprime le slash final
	var lastPart = href2.substring(href2.lastIndexOf('/') + 1);
  var part3 = href2.split('/')[3];
	console.log("href2",window.location);
	console.log("part3",part3);
	
	
	
	// Vérifie si l'URL contient #entreprises
  if (part3 === '#entreprises') {
	 
    function activateEntreprisesTab() {
      const $tab = $('#e-n-tab-title-1985465122');
      if ($tab.length && $tab.attr('aria-selected') !== 'true') {
        $tab.trigger('click');
        console.log('✅ Onglet "Entreprises" activé');
      }
		$("#mega-menu-item-3513").removeClass("mega-current-menu-item");//desactiver particulier
	  $("#mega-menu-item-1867").addClass("mega-current-menu-item");//activer entreprise
    }

    // Essaie immédiatement
    activateEntreprisesTab();

    // Réessaie si Elementor charge dynamiquement (observer DOM)
    const observer = new MutationObserver(() => {
      const $tab = $('#e-n-tab-title-1985465122');
      if ($tab.length && $tab.attr('aria-selected') !== 'true') {
        $tab.trigger('click');
        console.log('✅ Onglet "Entreprises" activé après chargement');
        observer.disconnect();
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    // Sécurité : retente après 500ms (Elementor est parfois lent)
    setTimeout(activateEntreprisesTab, 500);

  }

  if (part3 === '') {
	 
    function activateParticuliersTab() {
      const $tab = $('#pariculiers');
      if ($tab.length && $tab.attr('aria-selected') !== 'true') {
        $tab.trigger('click');
        console.log('✅ Onglet "Particulier" activé');
      }
		$("#mega-menu-item-1867").removeClass("mega-current-menu-item");//desactiver entreprise
	  $("#mega-menu-item-3513").addClass("mega-current-menu-item");//activer particulier
    }

    // Essaie immédiatement
    activateParticuliersTab();

    // Réessaie si Elementor charge dynamiquement (observer DOM)
    const observer = new MutationObserver(() => {
      const $tab = $('#pariculiers');
      if ($tab.length && $tab.attr('aria-selected') !== 'true') {
        $tab.trigger('click');
        console.log('✅ Onglet "Particulier" activé après chargement');
        observer.disconnect();
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    // Sécurité : retente après 500ms (Elementor est parfois lent)
    setTimeout(activateParticuliersTab, 500);

  }

	
	function activateParticulierTab() {
    const $tab = $('#pariculiers');
    if ($tab.length && $tab.attr('aria-selected') !== 'true') {
      $tab.trigger('click');
      console.log('✅ Onglet "Entreprises" activé');
    }
  }
	function activateEntrepriseTab() {
    const $tab = $('#e-n-tab-title-1985465122');
    if ($tab.length && $tab.attr('aria-selected') !== 'true') {
      $tab.trigger('click');
      console.log('✅ Onglet "Entreprises" activé');
    }
  }
  
  // Fonction pour le clic "Particuliers"
  $("#pariculiers").on("click", function() {
    console.log("Onglet Particuliers cliqué");
    $(".elementor-element.elementor-element-836bccd ")
        .css("background", "#ffffffff");
	  $("#mega-menu-item-1867").removeClass("mega-current-menu-item");//desactiver entreprise
	  $("#mega-menu-item-3513").addClass("mega-current-menu-item");//activer particulier
	  setTimeout(activateParticulierTab, 500);
  });

  // Fonction pour le clic "Entreprises"
  $("#e-n-tab-title-1985465122").on("click", function() {
    console.log("Onglet Entreprises cliqué");
    $(".elementor-element.elementor-element-836bccd ")
        .css("background", "#002f40");
	  $("#mega-menu-item-3513").removeClass("mega-current-menu-item");//desactiver particulier
	  $("#mega-menu-item-1867").addClass("mega-current-menu-item");//activer entreprise
	  setTimeout(activateEntrepriseTab, 500);
  });


  const selector = '#mega-menu-wrap-menu-1 #mega-menu-menu-1 > li.mega-menu-item > a.mega-menu-link';

  $(document).on('focus', selector, function(e){
    const $a = $(this);
    console.log('focus:', $a.text().trim());
    $(".elementor-element.elementor-element-836bccd ")
        .css("background", "#002f40");
	  $("#mega-menu-item-3513").removeClass("mega-current-menu-item");//desactiver particulier
	  $("#mega-menu-item-1867").addClass("mega-current-menu-item");//activer entreprise
	  setTimeout(activateEntrepriseTab, 500);
  });

  $("#mega-menu-item-1867 .mega-menu-link").on("click", function(e) {
      e.preventDefault(); // si tu veux empêcher le scroll vers #entreprises
      console.log("Onglet Entreprises cliqué");

      $(".elementor-element.elementor-element-836bccd ")
          .css("background", "#002f40");

      $("#mega-menu-item-3513").removeClass("mega-current-menu-item");
      $("#mega-menu-item-1867").addClass("mega-current-menu-item");

      setTimeout(activateEntrepriseTab, 500);
  });


});



