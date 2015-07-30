<?php // TODO Clean up IDs. Use standard Camel Case instead of dashes. ?>

<div id="page">
    <div id="main">
        <div id="content" class="container" >
            <div class="element-invisible"><a id="main-content"></a></div>
            <div id="content-inside" class="inside">
                <?php print render($page['content']); ?>
            </div>
        </div>
    </div>
    <!-- TODO : Footer tag issue on printing, investigate later  -->
    <div id="appendix" class="print-element">
        <h3>Appendix</h3>
    </div>
</div>

<?php print render($page['bottom']); ?>
