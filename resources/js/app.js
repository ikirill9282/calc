import "./bootstrap";
import AirDatepicker from "air-datepicker";
import "air-datepicker/air-datepicker.css";
import $ from "jquery";

let available_delivery_dates = [];
let available_pick_dates = [];

let datepicker1 = null;
let datepicker2 = null;
let datepicker3 = null;

function discoverItems() {
    $(".input-numeric").each((k, el) => {
        $(el).on("input", function (evt) {
            $(this).val(evt.target.value.replace(/[^0-9\.]+/g, ""));
            $(this).trigger("change");
        });
    });

    $(".boxes-item").each((k, el) => {
        const inputs = $(el)
            .find(".input")
            .on("change", function () {
                Livewire.dispatch("setField", {
                  name: $(this).siblings('input[type="hidden"]').attr("name"),
                  value: $(this).val(),
                });
            });
    });

    $(".counter").each((k, el) => {
        const input = $(el).find("input");
        const count = $(el).find(".count");
        const plus = $(el).find(".plus");
        const minus = $(el).find(".minus");
        const group = $(el).closest(".group");
        const radio = group.find('input[type="radio"]');

        let state = input.val();
        let send = false;
        let delay = null;

        count.html(state);

        const setSate = (val) => {
            state = val;
            count.html(state);
            input.val(state);
            Livewire.dispatch("setAddtionioal", {
                name: input.attr("name"),
                value: state,
            });
            // if (!send) {
            //     send = true;
            //     delay = setTimeout(() => {
            //         Livewire.dispatch("setAddtionioal", {
            //             name: input.attr("name"),
            //             value: state,
            //         });
            //         clearTimeout(delay);
            //     }, 500);
            // } else {
            //     clearTimeout(delay);
            //     delay = setTimeout(() => {
            //         Livewire.dispatch("setAddtionioal", {
            //             name: input.attr("name"),
            //             value: state,
            //         });
            //         clearTimeout(delay);
            //     }, 1000);
            // }
        };

        const hasChecked = () => radio.is(":checked");
        // const clearRelated = () => {
        //     const key = group.data("related");
        //     if (key) {
        //         const related = $(`[data-related="${key}"]`);
        //         related.each((k, e) => {
        //             e !== group
        //                 ? $(e).find(".counter").find(".count").html(0)
        //                 : null;
        //         });
        //     }
        // };

        plus.on("click", (evt) => {
            if (!hasChecked()) {
                radio.prop("checked", true);
                // clearRelated();
            }
            setSate((+state + 1));
        });

        minus.on("click", (evt) => {
            if (!hasChecked()) {
                radio.prop("checked", true);
                // clearRelated();
            }

            (+state - 1) >= 0 ? setSate((+state - 1)) : setSate(state);
        });

        radio.on("change", (evt) => clearRelated());

        // $(el).on("clear", () => setSate(0));
    });

    $(".open_auth").on("click", function (evt) {
        evt.preventDefault();
        Livewire.dispatch("openAuthModal");
    });

    $(".input-clear").on("click", function () {
        Livewire.dispatch("clearField", { name: $(this).data("name") });
    });

    $(".clear-input").on("click", function (evt) {
        evt.preventDefault();
        
        $(this).closest(".relative").find("input").val(null);
    });

    $('#agents-table').find('.agents-block').find('.title').each((k, el) => {
      $(el).off('click');
      $(el).on('click', function() {
        $(this).closest('.agents-block').find('.agents-toggle').slideToggle();
        $(this).closest('.agents-block').find('.icon').toggleClass('rotate-180');
      });
    });

    $('.time-message').each((k, el) => {
      setTimeout(() => {
        $(el).slideToggle(() => {
          $(el).detach();
          Livewire.dispatch('clearMessages');
        });
      }, 5000);
    });


    $('.order-details-toggle').each((k, el) => {
      $(el).off('click', () => null);
      $(el).on('click', function() {
        $(this).closest('.order-details').find('.order-details-view').slideToggle();

        if (!$(this).data('open')) {
          $(this).data('open', true);
          $(this).addClass('!opacity-100');
          $(this).find('.icon').addClass('rotate-180');
        } else {
          $(this).data('open', false);
          $(this).removeClass('!opacity-100');
          $(this).find('.icon').removeClass('rotate-180');
        }
      });
    })
}

document.addEventListener("DOMContentLoaded", function () {
    discoverItems();

    Livewire.dispatch('initDatepickers');

    Livewire.hook("morphed", function () {
        discoverItems();
    });

    Livewire.on("deliveryDates", (data) => {
        // available_delivery_dates = data[0];

        let el1 = document.getElementById("datepicker");
        let clone1 = el1.cloneNode(true);
        el1.parentNode.replaceChild(clone1, el1);

        datepicker1 = new AirDatepicker("#datepicker", {
            onRenderCell: ({ date, cellType }) => {
                const today = new Date();
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const year = date.getFullYear();
                    const month =
                        String(date.getMonth() + 1).length === 1
                            ? `0${date.getMonth() + 1}`
                            : date.getMonth() + 1;
                    const day =
                        String(date.getDate()).length === 1
                            ? `0${date.getDate()}`
                            : date.getDate();
                    const ddd = `${year}-${month}-${day}`;

                    if (!data[0].includes(ddd)) {
                        return response;
                    }
                }
            },
            onSelect: ({ date, formattedDate, datepicker }) => {
                Livewire.dispatch("setField", {
                    name: $(datepicker.$el)
                        .closest(".datepicker-group")
                        .data("name"),
                    value: formattedDate,
                });
                datepicker.hide();
            },
        });
    });

    Livewire.on("deliveryPickDates", (data) => {
        // available_delivery_dates = data[0];
        let el2 = document.getElementById("datepicker2");
        let clone2 = el2.cloneNode(true);
        el2.parentNode.replaceChild(clone2, el2);

        datepicker2 = new AirDatepicker("#datepicker2", {
            onSelect: ({ date, formattedDate, datepicker }) => {
                const elem = $(datepicker.$el)
                    .closest(".infoblock")
                    .find(".cargo-date");
                elem.find(".date").html(formattedDate);

                if (!elem.hasClass("collapsed")) {
                    elem.slideToggle();
                    elem.addClass("collapsed");
                }

                Livewire.dispatch("setField", {
                    name: $(datepicker.$el)
                        .closest(".datepicker-group")
                        .data("name"),
                    value: formattedDate,
                });
                datepicker.hide();
            },
            onRenderCell: ({ date, cellType }) => {
                const today = new Date();
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const year = date.getFullYear();
                    const month =
                        String(date.getMonth() + 1).length === 1
                            ? `0${date.getMonth() + 1}`
                            : date.getMonth() + 1;
                    const day =
                        String(date.getDate()).length === 1
                            ? `0${date.getDate()}`
                            : date.getDate();
                    const ddd = `${year}-${month}-${day}`;

                    if (!data[0].includes(ddd)) {
                        return response;
                    }
                }
            },
        });
    });

    Livewire.on("pickDates", (data) => {
        
        let el3 = document.getElementById("datepicker3");
        let clone3 = el3.cloneNode(true);
        el3.parentNode.replaceChild(clone3, el3);

        datepicker3 = new AirDatepicker("#datepicker3", {
            onRenderCell: ({ date, cellType }) => {
                const today = new Date();
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const year = date.getFullYear();
                    const month =
                        String(date.getMonth() + 1).length === 1
                            ? `0${date.getMonth() + 1}`
                            : date.getMonth() + 1;
                    const day =
                        String(date.getDate()).length === 1
                            ? `0${date.getDate()}`
                            : date.getDate();
                    const ddd = `${year}-${month}-${day}`;

                    if (!data[0].includes(ddd)) {
                        return response;
                    }
                }
            },
            onSelect: ({ date, formattedDate, datepicker }) => {
                Livewire.dispatch("setField", {
                    name: $(datepicker.$el)
                        .closest(".datepicker-group")
                        .data("name"),
                    value: formattedDate,
                });
                datepicker.hide();
            },
        });
    });

    // Theme Selector
    const theme_selector = document.querySelector("html");
    const theme_button = document.querySelector("#theme-button");

    const getThemeStatus = () =>
        window.localStorage
            ? window.localStorage.getItem("darkMode") === "true"
            : null;

    const setThemeStatus = (status) =>
        window.localStorage
            ? window.localStorage.setItem("darkMode", status)
            : null;

    const setThemeClass = () =>
        getThemeStatus()
            ? theme_selector.classList.add("dark")
            : theme_selector.classList.remove("dark");

    if (getThemeStatus()) {
        theme_button.checked = true;
    }
    setThemeClass();

    theme_button.addEventListener("change", () => {
        setThemeStatus(theme_button.checked);
        setThemeClass();
        axios.post("/api/theme", { darkMode: theme_button.checked });
    });
    // End Theme Selector

    // Toggle cargo inputs
    let cargo_open = false;
    $('input[name="transfer_method"]').on("change", function () {
        if (!cargo_open) {
            $(this).closest(".group").find(".infoblock").slideToggle();
            cargo_open = true;
        } else {
            $('input[name="transfer_method"]').each((k, el) => {
                $(el).closest(".group").find(".infoblock").slideToggle();
            });
        }

        Livewire.dispatch("setField", {
            name: $(this).attr("name"),
            value: $(this).val(),
        });
    });
    // End Toggle cargo inputs

    // Checkbox
    $(".checkbox-form-group").each((k, el) => {
        const target = $(el).find('input[type="checkbox"]');

        $(el)
            .find("label")
            .on("click", (evt) => evt.preventDefault());
        $(el).on("click", () => {
            $(target).prop("checked", !$(target).prop("checked"));
            $(el).closest(".checkbox-group").find(".infoblock").slideToggle();
            
            Livewire.dispatch("setField", {
              name: $(target).attr("name"),
              value: $(target).prop("checked"),
            });
        });
    });
    // End Checkbox

    // Input with helper buttons
    $(".input-helper-group").each((k, el) => {
        const target = $(el).find("input");
        const helpers = $(el).find(".inut-helper-item");

        helpers.on("click", function () {
            target.val($(this).html());
            const sibl = target.siblings('input[type="hidden"]');

            Livewire.dispatch("setField", {
                name: sibl.attr("name"),
                value: sibl.val(),
            });
        });
    });
    // End Input with helper buttons

    // Counter input

    // End Counter input

    // Distributors input
    $(".distributor-group").each((k, el) => {
        $(el)
            .find(".distributor-item")
            .each((key, elem) => {
                $(elem).on("click", function () {
                    const radio = $(this).find('input[type="radio"]');
                    radio.prop("checked", !radio.prop("checked"));
                    Livewire.dispatch('setField', {name: 'distributor_id', value: radio.val()})
                });
            });
    });
    // End Distributors input

    // Burger
    $("#burger").on("click", () => {
        $("#menu").addClass("collapsable");
        $("#menu").toggleClass("!translate-x-0");
        $("body").toggleClass("h-screen overflow-hidden");
    });

    $("#close-menu").on("click", () => {
        $("#menu").toggleClass("!translate-x-0");
        $("body").toggleClass("h-screen overflow-hidden");
    });

    $("#menu").on("click", function (evt) {
        if ($(this).hasClass("collapsable") && evt.target === this) {
            $("#menu").toggleClass("!translate-x-0");
            $("body").toggleClass("h-screen overflow-hidden");
        }
    });
    // End Burger

    // Dropdown component
    $(".dropdown-group").each((k, el) => {
        const input = $(el).find(".input");
        const fields = $(el).find(".dropdown-item");
        const wrap = $(el).find(".dropdown-wrap");
        const dropdown = $(el).find(".dropdown");

        const appendFields = (fields) => {
            const content = fields.length
                ? fields
                : '<span class="px-4 py-1">Нет доступных адресов</span>';
            wrap.empty();
            wrap.append(content);
        };

        const filterFields = (val) => {
            const filtered = fields.filter((key, field) =>
                $(field)
                    .data("value")
                    ?.toLowerCase()
                    .includes(val?.toLowerCase())
            );
            appendFields(filtered);
        };

        input.on("focus", () => {
            if (dropdown.hasClass("hidden")) {
                // dropdown.removeClass('hidden');
                dropdown.attr("data-shopen", true);
                dropdown.slideToggle(() => {
                    dropdown.removeClass("hidden");
                });
            }
        });
        input.on("focusout", () => {
            if (!dropdown.hasClass("hidden")) {
                dropdown.slideToggle(() => {
                    dropdown.addClass("hidden");
                    dropdown.attr("data-shopen", false);
                });
            }
        });

        if (input.data("filter")) {
            input.on("input", () =>
                input.val().length
                    ? filterFields(input.val())
                    : appendFields(fields)
            );
        }

        if (input.data("search")) {
            input.on("input", () => {
                Livewire.dispatch("setField", {
                    name: "user_focused_dropdown",
                    value: $(el).data("dropdown"),
                });
                setTimeout(() => {
                    Livewire.dispatch("setField", {
                        name: "user_address_query",
                        value: input.val(),
                    });
                }, 300);
            });
        }
    });
    // End Dropdown component
    
    $('.input-off').on('beforeinput', function(evt) {
      evt.preventDefault();
    });
});

window.addEventListener("fieldClean", (event) => {
    const params = event.detail[0];
    console.log(params);
    
    if (
        Object.keys(params).includes("type") &&
        params["type"] === "datepicker"
    ) {
        const elem = $(`[data-datepicker="${params["name"]}"]`);
        elem.val(null);
    }
    if (Object.keys(params).includes("type") && params["type"] === "dropdown") {
        const elem = $(`[data-dropdown="${params["name"]}"]`).find("input");
        elem.val(null);
    }

    if (!Object.keys(params).includes("type") || params["type"] === null) {
        const elem = $(`input[name="${params["name"]}"]`).siblings(".input");
        elem.val(null);
        if (params["name"] == "distributor_id") {
            const item = $(`input[name="${params["name"]}"]`);
            item.prop("checked", false);
        }
    }
});

window.addEventListener("fieldUpdated", (event) => {
    const params = event.detail[0];
    
    console.log(params);
    
    if (Object.keys(params).includes("type") && params["type"] === "dropdown") {
        const elem = $(`[data-dropdown="${params["name"]}"]`).find(".input");
        elem.val(params["value"]);
    }
    
    if (Object.keys(params).includes("type") && params["type"] === "datepicker") {
        const elem = $(`[data-dropdown="${params["name"]}"]`).find(".input");
        let clone = elem.cloneNode(true);
        clone.val(params["value"]);
        elem.parentNode.replaceChild(clone, elem);
        console.log('ok');
        
    }
});
