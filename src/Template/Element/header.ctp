<nav class="navbar navbar-toggleable-md navbar-inverse header">
  <button class="navbar-toggler navbar-toggler-left" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <ul class="navbar-nav">
    <li class="navbar-item">
      <a href="/" class="navbar-brand nav-item logo title">
        <span>Muncie</span><i id="logo-icon" class="icon-me-logo"></i><span>Events</span>
      </a>
      <ul id="mid-nav" class="navbar-nav">
        <?php echo $this->element('header/links_secondary'); ?>
      </ul>
      <a class="navbar-brand logo" id="tagline">
        <?php echo $this->element('header/tagline'); ?>
      </a>
    </li>
  </ul>
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div>
      <div id="mobile-search">
        <?php # echo $this->element('header/search'); ?>
      </div>
      <div class="header-nav">
        <ul id="res-nav" class="navbar-nav">
          <?php echo $this->element('header/links_secondary'); ?>
        </ul>
      </div>
      <div class="header-nav">
			  <?php echo $this->element('header/links_primary'); ?>
      </div>
    </div>
  </div>
  <ul class="navbar-nav" id="tagline-lg">
    <li class="navbar-item">
      <a class="navbar-brand logo">
        <?php echo $this->element('header/tagline'); ?>
        <?php echo $this->element('header/search'); ?>
      </a>
    </li>
  </ul>
</nav>
