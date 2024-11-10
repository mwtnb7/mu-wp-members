<?php

global $post;

the_post();

$content = apply_filters( 'the_content', $post->post_content, 99 );
echo $content;
