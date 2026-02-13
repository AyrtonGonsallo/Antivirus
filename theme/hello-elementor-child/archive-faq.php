<?php get_header(); ?>

<main class="archive-faq">

    <?php echo do_shortcode('[elementor-template id="10213"]'); ?>
    
    <div class="faq-search-result-container" id="scroll">
        <!--  Barre de recherche -->
        <div class="faq-search-wrapper" style="display:flex;justify-content:center;margin-bottom:60px;position: relative;width: 80%;margin-inline:auto;">
            <input type="text" id="faq-search-input" placeholder="Que recherchez-vous ?" style="border: 1px solid rgba(227, 227, 227, 1) !important;border-radius: 50px 50px 50px 50px !important; background-color: rgba(227, 227, 227, 1); padding: 4px 35px !important; height: 40px;"/>
            <button id="faq-search-btn" style="cursor: pointer;width: 28px;height: 40px;border-radius: 50px;display: flex;align-items: center;justify-content: center;background-color: #BFBFBF;border: none !important;position: absolute;right: 0;">
            <span class="innericon" style="display:block; height: 20px;">
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="22" height="22" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
					<path d="M460.355,421.59L353.844,315.078c20.041-27.553,31.885-61.437,31.885-98.037
						C385.729,124.934,310.793,50,218.686,50C126.58,50,51.645,124.934,51.645,217.041c0,92.106,74.936,167.041,167.041,167.041
						c34.912,0,67.352-10.773,94.184-29.158L419.945,462L460.355,421.59z M100.631,217.041c0-65.096,52.959-118.056,118.055-118.056
						c65.098,0,118.057,52.959,118.057,118.056c0,65.096-52.959,118.056-118.057,118.056C153.59,335.097,100.631,282.137,100.631,217.041
						z"></path>
				</svg>
		</span>
            </button>
        </div>

        <!--  Conteneur FAQ -->
        <div id="faq-container" class="faq-grid">
            <?php query_posts([
    'post_type' => 'faq',
    'orderby'   => 'menu_order',
    'order'     => 'ASC'
]);
            if ( have_posts() ) :
                while ( have_posts() ) : the_post(); ?>
                    <article class="faq-item">
                        <h4><?php the_title(); ?></h4>
                        <a class="" href="<?php the_permalink();?>">
                            <span class="">En savoir plus</span>
                            
                        </a>
                    </article>
                <?php endwhile;
            endif;
            ?>
        </div>

        <!-- ⬇ Bouton load more -->
        <div class="faq-load-more-wrapper">
            <button id="faq-load-more"
                    data-page="1"
                    data-max="<?php echo $wp_query->max_num_pages; ?>">
                Voir plus
            </button>
        </div>
    </div>


    <?php echo do_shortcode('[elementor-template id="10243"]'); ?>

</main>

<?php get_footer(); ?>


<script>
    jQuery(function ($) {

        jQuery(function($){

    function loadMore(page, keyword = '') {
        $.ajax({
            url: faqAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'faq_load_more',
                page: page,
                keyword: keyword
            },
            beforeSend: function(){
                $('#faq-load-more').text('Chargement...');
            },
            success: function(res){
                if(page == 1){
                    $('#faq-container').html(res); // nouvelle recherche
                } else {
                    $('#faq-container').append(res); // ajout load more
                }

                $('#faq-load-more').text('Voir plus');
                const maxPage = parseInt($('#faq-load-more').data('max'));
                if(page >= maxPage || res.trim() == '') $('#faq-load-more').hide();
            },
            error: function(){
                $('#faq-load-more').text('Erreur');
            }
        });
    }

    // Click sur loupe
    $('#faq-search-btn').on('click', function(){
        const keyword = $('#faq-search-input').val().trim();
        $('#faq-load-more').data('page', 1).show(); // reset
        loadMore(1, keyword);
    });

    // Enter key
    $('#faq-search-input').on('keypress', function(e){
        if(e.which == 13) $('#faq-search-btn').click();
    });

    // Click load more
    $('#faq-load-more').on('click', function(){
        const nextPage = parseInt($(this).data('page')) + 1;
        const keyword = $('#faq-search-input').val().trim();
        $(this).data('page', nextPage);
        loadMore(nextPage, keyword);
    });

});


    });



</script>