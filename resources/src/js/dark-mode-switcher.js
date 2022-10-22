(function () {
    "use strict";

    // stupidity commented out below -- why the deuce is it written like this?
    // Dark mode switcher

    // $(".dark-mode-switcher").on("click", function () {
    //     let switcher = $(this).find(".dark-mode-switcher__toggle");
    //     if ($(switcher).hasClass("dark-mode-switcher__toggle--active")) {
    //         $(switcher).removeClass("dark-mode-switcher__toggle--active");
    //     } else {
    //         $(switcher).addClass("dark-mode-switcher__toggle--active");
    //     }
    //
    //     setTimeout(() => {
    //         let link = $(".dark-mode-switcher").data("url");
    //         window.location.href = link;
    //     }, 500);
    // });

    $(".dark-mode-switcher").on("click", function () {
        let switcher = $(this).find(".dark-mode-switcher__toggle");
        if ($(switcher).hasClass("dark-mode-switcher__toggle--active")) {
            $(switcher).removeClass("dark-mode-switcher__toggle--active");
        } else {
            $(switcher).addClass("dark-mode-switcher__toggle--active");
        }
        if ($("html").hasClass("dark")) {
            $("html").removeClass("dark").addClass("light");
        } else {
            $("html").removeClass("light").addClass("dark");
        }
    });
})();
