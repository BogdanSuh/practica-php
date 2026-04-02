<h1>Списсок статей</h1>
<ol>
    <?php
    foreach ($posts as $post) {
        echo '<li>' . $post->title . '</li>';
    }
    ?>
</ol>