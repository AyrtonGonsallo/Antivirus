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

	/*************************** mega menu overlay ****************************/
   const parents = document.querySelectorAll('li.mega-menu-item');

    parents.forEach(parent => {
        const submenu = parent.querySelector('ul.mega-sub-menu');

        if (submenu) {
            parent.addEventListener('mouseenter', () => {
                document.body.classList.add('menu-open');
            });

            parent.addEventListener('mouseleave', () => {
                document.body.classList.remove('menu-open');
            });
        }
    });

/********************** plugin reviews date placement ********************************/
document.querySelectorAll(".cr-review-card").forEach(card => {
        
        const verified = card.querySelector(".reviewer-verified");
        const datetime = card.querySelector(".datetime");
        
        // Vérifier que les deux éléments existent
        if (verified && datetime) {
            // Remplacer le HTML interne
            verified.innerHTML = datetime.innerHTML;

            // Cacher l'élément datetime original
            datetime.style.display = "none";
        }
    });

	
});

/******************************************** option delete first select ******************************************/
//     function autoSelectFirstOption() {
//         const selects = document.querySelectorAll('select[data-attribute_name]');

//         selects.forEach(select => {
//             const emptyOption = select.querySelector('option[value=""]');
//             const firstRealOption = select.querySelector('option[value]:not([value=""])');

//             // Masquer "Choisir une option"
//             if (emptyOption) emptyOption.style.display = "none";

//             // Sélectionner automatiquement la 1ère option valable
//             if (firstRealOption && select.value === "") {
//                 select.value = firstRealOption.value;
//                 select.dispatchEvent(new Event('change', { bubbles: true }));
//             }
//         });
//     }

//     // Exécuter une première fois
//     autoSelectFirstOption();

//     // Observer les changements WooCommerce
//     const observer = new MutationObserver(autoSelectFirstOption);
//     observer.observe(document.body, { childList: true, subtree: true });


/*****************************/
// --- 1. Masquer "Choisir une option" et sélectionner la première option ---
//     jQuery('select[data-attribute_name]').each(function() {
//         const select = jQuery(this);
//         const firstRealOption = select.find('option[value!=""]').first();

//         select.find('option[value=""]').hide();

//         if (firstRealOption.length) {
//             select.val(firstRealOption.val()).trigger('change');
//         }
//     });

   /*************************/


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
/****************************************************************************************/
/***************************** WooCommerce Breadcrumb + Custom Separator ***************************************/
document.addEventListener('DOMContentLoaded', function () {
  const IMAGE_CLASS = 'custom-wc-breadcrumb-home-icon';
  const imageSrc = "/wp-content/uploads/2025/10/home-3.webp"; // image home
  const NEW_SEPARATOR = " > "; // ton nouveau séparateur

  function insertImageBeforeHomeWC() {
    const breadcrumb = document.querySelector('.woocommerce-breadcrumb');
    if (!breadcrumb) return;

    /* ---- 1) Remplacer les séparateurs ---- */
    breadcrumb.innerHTML = breadcrumb.innerHTML.replace(/&nbsp;\/&nbsp;/g, NEW_SEPARATOR);

    /* ---- 2) Ajouter image avant "Accueil" ---- */
    let homeLink =
      breadcrumb.querySelector('a[href="https://test.antivirusedition.com"]') ||
      breadcrumb.querySelector('a[href="/"]') ||
      breadcrumb.querySelector('a');

    if (!homeLink) return;

    // éviter doublons
    const prev = homeLink.previousElementSibling;
    if (prev && prev.classList && prev.classList.contains(IMAGE_CLASS)) return;

    // créer image
    const img = document.createElement('img');
    img.src = imageSrc;
    img.alt = "Accueil";
    img.className = IMAGE_CLASS;
    img.style.width = '16px';
    img.style.height = '16px';
    img.style.marginRight = '6px';
    img.style.marginTop = '-6px';
    img.style.verticalAlign = 'middle';

    // insérer avant le lien
    homeLink.parentNode.insertBefore(img, homeLink);
  }

  // lancer une première fois
  insertImageBeforeHomeWC();

  // observer WooCommerce si le DOM change
  const wcBreadcrumb = document.querySelector('.woocommerce-breadcrumb');
  if (wcBreadcrumb) {
    const observer = new MutationObserver(function () {
      insertImageBeforeHomeWC();
    });
    observer.observe(wcBreadcrumb, { childList: true, subtree: true });
  } else {
    // fallback si le fil d'ariane arrive plus tard
    const bodyObserver = new MutationObserver(function (mutations, obs) {
      if (document.querySelector('.woocommerce-breadcrumb')) {
        insertImageBeforeHomeWC();
        const wc = document.querySelector('.woocommerce-breadcrumb');
        const observer = new MutationObserver(insertImageBeforeHomeWC);
        observer.observe(wc, { childList: true, subtree: true });
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
//   $("#e-n-tab-title-1985465122").on("click", function() {
//     console.log("Onglet Entreprises cliqué");
//     $(".elementor-element.elementor-element-836bccd ")
//         .css("background", "#002f40");
// 	  $("#mega-menu-item-3513").removeClass("mega-current-menu-item");//desactiver particulier
// 	  $("#mega-menu-item-1867").addClass("mega-current-menu-item");//activer entreprise
// 	  setTimeout(activateEntrepriseTab, 500);
//   });


//   const selector = '#mega-menu-wrap-menu-1 #mega-menu-menu-1 > li.mega-menu-item > a.mega-menu-link';

//   $(document).on('focus', selector, function(e){
//     const $a = $(this);
//     console.log('focus:', $a.text().trim());
//     $(".elementor-element.elementor-element-836bccd ")
//         .css("background", "#002f40");
// 	  $("#mega-menu-item-3513").removeClass("mega-current-menu-item");//desactiver particulier
// 	  $("#mega-menu-item-1867").addClass("mega-current-menu-item");//activer entreprise
// 	  setTimeout(activateEntrepriseTab, 500);
//   });

//   $("#mega-menu-item-1867 .mega-menu-link").on("click", function(e) {
//       e.preventDefault(); // si tu veux empêcher le scroll vers #entreprises
//       console.log("Onglet Entreprises cliqué");

//       $(".elementor-element.elementor-element-836bccd ")
//           .css("background", "#002f40");

//       $("#mega-menu-item-3513").removeClass("mega-current-menu-item");
//       $("#mega-menu-item-1867").addClass("mega-current-menu-item");

//       setTimeout(activateEntrepriseTab, 500);
//   });




/********************************* ACTIVE ENTREPRISE / PARTICULIER **************************************/

jQuery(document).ready(function($) {

    let switching = false; // empêche les doubles clics rapides

    function safeActivate($tab) {
        if (switching) return;
        switching = true;

        if ($tab.length && $tab.attr('aria-selected') !== 'true') {
            $tab.trigger('click');
        }

        setTimeout(() => switching = false, 200); // anti-spam
    }

    function activateEntrepriseUI() {
        $(".elementor-element.elementor-element-836bccd").css("background", "#002f40");
        $("#mega-menu-item-3513").removeClass("mega-current-menu-item");
        $("#mega-menu-item-1867").addClass("mega-current-menu-item");
    }

    function activateParticulierUI() {
        $(".elementor-element.elementor-element-836bccd").css("background", "#ffffff");
        $("#mega-menu-item-1867").removeClass("mega-current-menu-item");
        $("#mega-menu-item-3513").addClass("mega-current-menu-item");
    }

    const $tabParticulier = $("#pariculiers");
    const $tabEntreprise = $("#e-n-tab-title-1985465122");

    /*** ---- Gestion URL ---- ***/
    const part3 = window.location.pathname.split("/")[3];

    if (part3 === "#entreprises") {
        activateEntrepriseUI();
        safeActivate($tabEntreprise);
    }

    if (part3 === "" || part3 === undefined) {
        activateParticulierUI();
        safeActivate($tabParticulier);
    }

    /*** ---- Clic sur tab Particuliers ---- ***/
    $tabParticulier.on("click", function () {
        activateParticulierUI();
        safeActivate($tabParticulier);
    });

    /*** ---- Clic sur tab Entreprises ---- ***/
    $tabEntreprise.on("click", function () {
        activateEntrepriseUI();
        safeActivate($tabEntreprise);
    });

    /*** ---- Clic menu entreprise ---- ***/
    $("#mega-menu-item-1867 .mega-menu-link").on("click", function (e) {
        e.preventDefault();
        activateEntrepriseUI();
        safeActivate($tabEntreprise);
    });

    /*** ---- Focus menu (tab clavier) ---- ***/
    const selector = '#mega-menu-wrap-menu-1 #mega-menu-menu-1 > li.mega-menu-item > a.mega-menu-link';
    $(document).on('focus', selector, function () {
        activateEntrepriseUI();
        safeActivate($tabEntreprise);
    });

});


 });


/********************************** resize les images du popup panier a droite ****************************************/
jQuery(document).ready(function($){
    function replaceCartImages(){
        $('.elementor-menu-cart__product-image img').each(function(){
            var img = $(this);
            var src = img.attr('src');
            if(src){
                var fullSrc = src.replace(/-\d+x\d+(\.\w+)$/, '$1');
                img.attr('src', fullSrc);
                img.css({
                    'width': 'auto',
                    'height': 'auto',
                    'max-width': '100%',
                    'object-fit': 'contain'
                });
            }
        });
    }

    // Attendre que le panier soit visible
    setTimeout(replaceCartImages, 1000);

    // Réappliquer après mise à jour AJAX du panier
    $(document).on('updated_cart_widget', function(){
        replaceCartImages();
    });
});

/********************************** resize les images en related post **************************************/
jQuery(document).ready(function($){

    function resizeRelatedProductsImages(){
        jQuery('.elementor-products-grid .product img').each(function(){
            var img = $(this);
            var src = img.attr('src');

            if(src){
                // Remplacer l'image par l'originale (supprimer le suffixe -WxH)
                var fullSrc = src.replace(/-\d+x\d+(\.\w+)$/, '$1');
                img.attr('src', fullSrc);
                img.removeAttr('srcset'); // supprime les miniatures WooCommerce

                // Appliquer le style
                img.css({
                    'width': '80%',      // largeur relative
                    'height': '291px',   // hauteur fixe
                    'object-fit': 'contain', // remplissage sans déformation
                    'display': 'block',
                    'margin': '0 auto'  // centrer horizontalement
                });
            }
        });
    }

    // Appliquer au chargement
    resizeRelatedProductsImages();

    // Réappliquer après mise à jour AJAX si nécessaire
    jQuery(document).on('updated_cart_widget', function(){
        resizeRelatedProductsImages();
    });
});
/************************************* resize les images de la page panier ******************************************/
jQuery(document).ready(function($){

    function resizeCartImages(){
        $('.shop_table.cart img, .woocommerce-cart-form__cart-item img').each(function(){
            var img = $(this);
            var src = img.attr('src');
            if(src){
                // Remplacer la miniature par l'image originale si nécessaire
                var fullSrc = src.replace(/-\d+x\d+(\.\w+)$/, '$1');
                img.attr('src', fullSrc);
                img.removeAttr('srcset');

                // Appliquer la taille fixe et contain
                img.css({
                    'width': '100px',
                    'height': '71px',
                    'object-fit': 'contain',
                    'display': 'block',
                    'margin': '0px'
                });
            }
        });
    }

    // Appliquer après chargement
    setTimeout(resizeCartImages, 500);
    setTimeout(resizeCartImages, 1000);

    // Réappliquer après mises à jour AJAX du panier
    $(document).on('updated_cart_widget updated_wc_div', function(){
        resizeCartImages();
    });

    // Observer les ajouts dynamiques
    var observer = new MutationObserver(function(mutations){
        resizeCartImages();
    });
    $('body').each(function(){
        observer.observe(this, { childList: true, subtree: true });
    });

});
/************************************************ code globale pour le size des images ****************************************************/
/************************************ select languages menu ***********************************/
   const wrapper = document.querySelector(".trp-flags-hover");
const trigger = wrapper.querySelector(".trp-hover-icon");
const menu = wrapper.querySelector(".trp-hover-menu222");

if (trigger && menu) {
    // Toggle au clic sur l'icône
    trigger.addEventListener("click", function (e) {
        e.stopPropagation(); // Empêche le clic de remonter
        if (menu.style.display === "block") {
            menu.style.display = "none";
        } else {
            menu.style.display = "block";
        }
    });

    // Fermer si on clique en dehors du wrapper
    document.addEventListener("click", function (e) {
        if (!wrapper.contains(e.target)) {
            menu.style.display = "none";
        }
    });

    // Fermer quand la souris sort du wrapper
    wrapper.addEventListener("mouseleave", function () {
        menu.style.display = "none";
    });
}

/***********************************************************************/
jQuery(document).ready(function($){

    // Quand l'utilisateur clique sur "Se connecter"
    $(document).on('click', '.cr-review-form-continue', function () {
      console.log("produit stocké")

        // On stocke l'URL actuelle (page produit)
        localStorage.setItem('redirect_after_login', window.location.href);

    });

});














