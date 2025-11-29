(function ($) {
  "use strict";
  $(function () {
    var body = $("body");
    var contentWrapper = $(".content-wrapper");
    var scroller = $(".container-scroller");
    var footer = $(".footer");
    var sidebar = $(".sidebar");

    //Add active class to nav-link based on url dynamically
    //Active class can be hard coded directly in html file also as required

    function addActiveClass(element) {
      var href = element.attr("href");
      if (!href) return false;
      
      // Remove protocol and domain from href if present
      var hrefPath = href.replace(/^https?:\/\/[^\/]+/, "");
      var currentPath = location.pathname;
      
      // Normalize paths (remove trailing slashes)
      hrefPath = hrefPath.replace(/\/$/, "");
      currentPath = currentPath.replace(/\/$/, "");
      
      // Check if current path matches href exactly or starts with href (for nested routes)
      var isMatch = currentPath === hrefPath || currentPath.startsWith(hrefPath + "/");
      
      if (isMatch) {
        element.parents(".nav-item").last().addClass("active");
        element.addClass("active");
        
        // Only open collapse if this is a sub-menu link and the collapse isn't already shown
        if (element.parents(".sub-menu").length) {
          var collapse = element.closest(".collapse");
          // Only add 'show' if it's not already there (respect Blade's initial state)
          if (!collapse.hasClass("show")) {
            collapse.addClass("show");
          }
        }
        
        if (element.parents(".submenu-item").length) {
          element.addClass("active");
        }
      }
      
      return isMatch;
    }

    var current = location.pathname
      .split("/")
      .slice(-1)[0]
      .replace(/^\/|\/$/g, "");
    
    // Track which collapses we've already opened to prevent opening multiple
    var openedCollapses = {};
    
    // First pass: mark all active links without opening collapses
    $(".nav li a", sidebar).each(function () {
      var $this = $(this);
      var href = $this.attr("href");
      if (!href) return;
      
      var hrefPath = href.replace(/^https?:\/\/[^\/]+/, "").replace(/\/$/, "");
      var currentPath = location.pathname.replace(/\/$/, "");
      var isMatch = currentPath === hrefPath || currentPath.startsWith(hrefPath + "/");
      
      if (isMatch) {
        $this.parents(".nav-item").last().addClass("active");
        $this.addClass("active");
      }
    });
    
    // Second pass: open only the collapse of the exact matching link
    $(".nav li a", sidebar).each(function () {
      var $this = $(this);
      if (!$this.hasClass("active")) return;
      
      // Only open collapse if this is a sub-menu link
      if ($this.parents(".sub-menu").length) {
        var collapse = $this.closest(".collapse");
        var collapseId = collapse.attr("id");
        
        // Only open if we haven't opened this collapse yet
        if (collapseId && !openedCollapses[collapseId]) {
          if (!collapse.hasClass("show")) {
            collapse.addClass("show");
          }
          openedCollapses[collapseId] = true;
        }
      }
    });

    $(".horizontal-menu .nav li a").each(function () {
      var $this = $(this);
      addActiveClass($this);
    });

    //Close other submenu in sidebar on opening any

    sidebar.on("show.bs.collapse", ".collapse", function () {
      sidebar.find(".collapse.show").collapse("hide");
    });

    $(".aside-toggler").on("click", function () {
      $(".mail-sidebar,.chat-list-wrapper").toggleClass("menu-open");
    });

    //Change sidebar and content-wrapper height
    applyStyles();

    function applyStyles() {
      //Applying perfect scrollbar
      if (!body.hasClass("rtl")) {
        if ($(".settings-panel .tab-content .tab-pane.scroll-wrapper").length) {
          const settingsPanelScroll = new PerfectScrollbar(
            ".settings-panel .tab-content .tab-pane.scroll-wrapper"
          );
        }
        if ($(".chats").length) {
          const chatsScroll = new PerfectScrollbar(".chats");
        }
        if (body.hasClass("sidebar-fixed")) {
          var fixedSidebarScroll = new PerfectScrollbar("#sidebar .nav");
        }
      }
    }

    $('[data-toggle="minimize"]').on("click", function () {
      if (
        body.hasClass("sidebar-toggle-display") ||
        body.hasClass("sidebar-absolute")
      ) {
        body.toggleClass("sidebar-hidden");
      } else {
        body.toggleClass("sidebar-icon-only");
      }
    });

    //checkbox and radios
    $(".form-check label,.form-radio label").append(
      '<i class="input-helper"></i>'
    );

    //fullscreen
    $("#fullscreen-button").on("click", function toggleFullScreen() {
      if (
        (document.fullScreenElement !== undefined &&
          document.fullScreenElement === null) ||
        (document.msFullscreenElement !== undefined &&
          document.msFullscreenElement === null) ||
        (document.mozFullScreen !== undefined && !document.mozFullScreen) ||
        (document.webkitIsFullScreen !== undefined &&
          !document.webkitIsFullScreen)
      ) {
        if (document.documentElement.requestFullScreen) {
          document.documentElement.requestFullScreen();
        } else if (document.documentElement.mozRequestFullScreen) {
          document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullScreen) {
          document.documentElement.webkitRequestFullScreen(
            Element.ALLOW_KEYBOARD_INPUT
          );
        } else if (document.documentElement.msRequestFullscreen) {
          document.documentElement.msRequestFullscreen();
        }
      } else {
        if (document.cancelFullScreen) {
          document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
          document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }
      }
    });
    var proBanner = document.querySelector("#proBanner");
    var navbar = document.querySelector(".navbar");
    var pageBodyWrapper = document.querySelector(".page-body-wrapper");
    var bannerClose = document.querySelector("#bannerClose");

    // Só aplicar lógica do banner se ele existir no DOM
    if (proBanner) {
      if ($.cookie("connectplus-free-banner") != "true") {
        proBanner.classList.add("d-flex");
        if (navbar) navbar.classList.remove("fixed-top");
      } else {
        proBanner.classList.add("d-none");
        if (navbar) navbar.classList.add("fixed-top");
      }

      if ($(".navbar").hasClass("fixed-top")) {
        if (pageBodyWrapper) pageBodyWrapper.classList.remove("pt-0");
        if (navbar) navbar.classList.remove("pt-5");
      } else {
        if (pageBodyWrapper) pageBodyWrapper.classList.add("pt-0");
        if (navbar) {
          navbar.classList.add("pt-5");
          navbar.classList.add("mt-3");
        }
      }
      
      if (bannerClose) {
        bannerClose.addEventListener("click", function () {
          proBanner.classList.add("d-none");
          proBanner.classList.remove("d-flex");
          if (navbar) {
            navbar.classList.remove("pt-5");
            navbar.classList.add("fixed-top");
            navbar.classList.remove("mt-3");
          }
          if (pageBodyWrapper) {
            pageBodyWrapper.classList.add("proBanner-padding-top");
          }
          var date = new Date();
          date.setTime(date.getTime() + 24 * 60 * 60 * 1000);
          $.cookie("connectplus-free-banner", "true", { expires: date });
        });
      }
    } else {
      // Se não há banner, garantir que o navbar está fixo e sem espaçamento extra
      if (navbar) {
        navbar.classList.add("fixed-top");
        navbar.classList.remove("pt-5");
        navbar.classList.remove("mt-3");
      }
      if (pageBodyWrapper) {
        pageBodyWrapper.classList.remove("pt-0");
      }
    }
  });
})(jQuery);

