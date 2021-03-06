<?php
  global $wp_query;

  TemplateEngine::get()->render(
    'jumbotron',
    array(
      'show_header' => false,
      'extra_class' => 'small-jumbotron',
      'algolia_search' => get_search_form($echo = false)
    )
  );

  $guide = \RepoManager::getGuideRepository()->getByPost(
    $wp_query->post,
    $include_similar = true
  );

  $allGuides = \RepoManager::getGuideRepository()->getList();
  $sidebarLinks = array();

  $first_aid = CustomPageManager::getFirstAidPage()->getPage();
  $sidebarLinks[] = array(
      'text' => 'Prim Ajutor',
      'href' => $first_aid->getPermalink(),
  );

  foreach ($allGuides as $g) {
    if ($g->getID() === $guide->getID()) {
        continue;
    }

    $sidebarLinks[] =  array(
      'text' => $g->getNume(),
      'href' => $g->getPermalink()
    );
  }

  $gallery = array();
  $is_first = true;
  $count = 0;
  foreach ($guide->getGalerieFoto() as $photo) {
    $gallery[] = array(
      'photo' => $photo,
      'idx' => $count,
      'first' => $is_first,
    );

    if ($is_first) {
      $is_first = false;
    }
    $count++;
  }

  TemplateEngine::get()->render(
    'guide',
    array(
      'title' => $guide->getNume(),
      'before_content' => $guide->getInainteaEvenimentului(),
      'is_before_single' => $guide->getInainteaEvenimentului()
        && !$guide->getInTimpulEvenimentului()
        && !$guide->getDupaEveniment(),
      'during_content' => $guide->getInTimpulEvenimentului(),
      'is_during_single' => $guide->getInTimpulEvenimentului()
        && !$guide->getInainteaEvenimentului()
        && !$guide->getDupaEveniment(),
      'after_content' => $guide->getDupaEveniment(),
      'is_after_single' => $guide->getDupaEveniment()
        && !$guide->getInainteaEvenimentului()
        && !$guide->getInTimpulEvenimentului(),
      'extra_info' => $guide->getInformatiiAditionale(),
      'video' => $guide->getVideoAjutator(),
      'photo_gallery' => $gallery,
      'photo_gallery_is_single' => count($gallery) === 1,
      'has_extra_info' => (
        $guide->getInformatiiAditionale()
        || $guide->getVideoAjutator()
        || $gallery
      ),
      'pdf_guide' => $guide->getGuidePDF(),
      'pdf_size' => $guide->getPDFGuideSize(),
      'sidebar_links' => $sidebarLinks,
    )
  );

  $recommendedGuides = $guide->getSimilarGuides();
  if (!$recommendedGuides) {
    $recommendedGuides = array_slice($allGuides, 0, 2);
  }

  $guideProps = array();

  foreach ($recommendedGuides as $guide) {
    $guideProps[] = array(
      'icon' => $guide->getPictograma()->getUrl(),
      'title' => $guide->getTitle(),
      'permalink' => $guide->getPermalink(),
      'see_more' => false,
      'color' => $guide->getCuloareGhid(),
      'id' => 'icon-' . preg_replace("/[^a-zA-Z0-9]+/", '', $guide->getTitle()),
      'is_svg' => $guide->getPictograma()->getMimeType() === 'image/svg+xml',
      'count_videos' => $guide->getVideoAjutator() ? 1 : 0,
      'count_photo' => count($guide->getGalerieFoto()),
    );
  }

  TemplateEngine::get()->render(
    'guide_listing',
    array(
      'guides' => $guideProps,
      'title' => 'Alte situații',
      'bg' => '#fff',
      'center' => true
    )
  );
?>
