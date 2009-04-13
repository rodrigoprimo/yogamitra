        <div id="menu">
            <ul><?php wp_list_pages('sort_column=menu_order&title_li='); ?></ul>
        </div>
    </div>
<!-- <div id="footer">
        <p>
            <?php printf(__('%1$s is proudly powered by %2$s', 'kubrick'), get_bloginfo('name'),
            '<a href="http://wordpress.org/">WordPress</a>'); ?>
            <br /><?php printf(__('%1$s and %2$s.', 'kubrick'), '<a href="' . get_bloginfo('rss2_url') . '">' . __('Entries (RSS)', 'kubrick') . '</a>', '<a href="' . get_bloginfo('comments_rss2_url') . '">' . __('Comments (RSS)', 'kubrick') . '</a>'); ?>
        </p>
    </div> -->
</div>

<?php wp_footer(); ?>
</body>
</html>
