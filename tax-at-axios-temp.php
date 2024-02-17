<?php
/**
 * The template for displaying archive of Amministrazione Trasparente
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#archive
 *
 * @package Design_Scuole_Italia
 */

get_header();

?>

<main id="main-container" class="main-container petrol">
    <?php get_template_part("template-parts/common/breadcrumb"); ?>
    <?php require_once WP_PLUGIN_DIR . '/import_at/index.php'; ?>
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row variable-gutters">
                <div class="col-lg-12 ">
                    <div class="section-title">
                        <?php the_archive_title_axios(); ?>
                    </div><!-- /title-section -->
                </div><!-- /col-lg-5 col-md-8 offset-lg-2 -->

            </div><!-- /row -->
        </div><!-- /container -->
    </section><!-- /section -->



    <section class="section bg-white border-top border-bottom d-block d-lg-none">
        <div class="container d-flex justify-content-between align-items-center py-3">
            <h3 class="h6 text-uppercase mb-0 label-filter"><strong>
                    <?php _e("Filtri", "design_scuole_italia"); ?>
                </strong></h3>
            <a class="toggle-search-results-mobile toggle-menu menu-search push-body mb-0" href="#">
                <svg class="svg-filters">
                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#svg-filters"></use>
                </svg>
            </a>
        </div>
    </section>
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row variable-gutters sticky-sidebar-container">
                <div class="col-lg-3 bg-white bg-white-left">
                    <?php get_template_part("template-parts/search/filters", "amministrazione"); ?>
                </div>
                <div class="col-lg-7 offset-lg-1 pt84">
                    <?php
                    function getLastPartOfUrl()
                    {
                        $url = $_SERVER['REQUEST_URI'];
                        $parts = explode('/', rtrim($url, '/'));
                        $lastPart = end($parts);
                        $lastPart = str_replace("-", " ", $lastPart);
                        return $lastPart;
                    }
                    function checkCategory($json, $innerArray)
                    {
                        $lastPartUrl = getLastPartOfUrl();
                        foreach ($json as $item) {
                            $category = strtolower($item['category']);
                            if ($category === $lastPartUrl || isChildCategory($innerArray, $lastPartUrl, $category)) {
                                ?>
                                <a class="presentation-card-link" href="#" data-element="service-link">
                                    <article class="card card-bg card-article card-article-<?php echo $class; ?> cursorhand">
                                        <div class="card-body">
                                            <div class="card-article-img">
                                                <div class="date">
                                                    <span class="year">
                                                        <?php echo date("Y"); ?>
                                                    </span>
                                                    <span class="day">
                                                        <?php echo date("d"); ?>
                                                    </span>
                                                    <span class="month">
                                                        <?php echo date("M"); ?>
                                                    </span>
                                                </div>
                                                <?php if (!$image_url) { ?>
                                                    <svg class="icon-<?php echo $class; ?> svg-<?php echo $icon; ?>">
                                                        <use xmlns:xlink="http://www.w3.org/1999/xlink"
                                                            xlink:href="#svg-<?php echo $icon; ?>"></use>
                                                    </svg>
                                                <?php } ?>
                                            </div>
                                            <div class="card-article-content">
                                                <h2 class="h3">
                                                    <?php echo $item['title']; ?>
                                                </h2>
                                                <p>
                                                    <?php echo $item['title']; ?>
                                                </p>
                                            </div><!-- /card-avatar-content -->
                                        </div><!-- /card-body -->
                                    </article><!-- /card card-bg card-article -->
                                </a>
                                <?php

                            }
                        }
                    }
                    ;
                    // Funzione per controllare se una categoria è figlia di un'altra categoria
                    function isChildCategory($innerArray, $parentCategory, $category)
                    {
                        foreach ($innerArray as $inner) {
                            if (strtolower($inner[0]) === $parentCategory && in_array(strtolower($category), array_map('strtolower', $inner[1]))) {
                                return true;
                            }
                        }
                        return false;
                    }
                    $json = json_decode(getData(), true);
                    $innerArray = dsi_amministrazione_trasparente_array();
                    checkCategory($json, $innerArray);
                    ?>
                </div><!-- /col-lg-8 -->
            </div><!-- /row -->
        </div><!-- /container -->
    </section>


</main>

<?php
get_footer();
