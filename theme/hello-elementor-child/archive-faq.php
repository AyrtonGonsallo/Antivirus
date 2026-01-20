<?php get_header(); ?>

<main class="archive-faq">

    <?php echo do_shortcode('[elementor-template id="10213"]'); ?>
    
    <div class="faq-search-result-container">
        <!--  Barre de recherche -->
        <div class="faq-search-wrapper" style="display:flex;justify-content:center;margin-bottom:40px;">
            <input type="text" id="faq-search-input" placeholder="Rechercher une question..." style="padding:12px 16px; font-size:16px; width:300px;"/>
            <button id="faq-search-btn" style="margin-left:10px; padding:12px 16px; cursor:pointer; font-size:16px;">
                🔍
            </button>
        </div>

        <!--  Conteneur FAQ -->
        <div id="faq-container" class="faq-grid">
            <?php query_posts([
    'post_type' => 'faq',
    'orderby'   => 'title',
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
                En savoir plus
            </button>
        </div>
    </div>


    <?php echo do_shortcode('[elementor-template id="3483"]'); ?>
    <?php echo do_shortcode('[elementor-template id="4153"]'); ?>
    <?php echo do_shortcode('[elementor-template id="3490"]'); ?>
    <?php echo do_shortcode('[elementor-template id="3493"]'); ?>

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

                $('#faq-load-more').text('En savoir plus');
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