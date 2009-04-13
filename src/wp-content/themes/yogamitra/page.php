<?php get_header(); ?>

    <div id="postarea">

    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>

            <div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
                <div class="entry">
                    <?php the_content(__('Read the rest of this entry &raquo;', 'kubrick')); ?>
                </div>
            </div>

        <?php endwhile; ?>
    <?php endif; ?>

    </div>

<?php get_footer(); ?>