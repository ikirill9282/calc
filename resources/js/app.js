import "./bootstrap";
import AirDatepicker from "air-datepicker";
import "air-datepicker/air-datepicker.css";
import $ from "jquery";

let available_delivery_dates = [];
let available_pick_dates = [];

let datepicker1 = null;
let datepicker2 = null;
let datepicker3 = null;


const show_password_btn = [];

const dropdowns = [...document.querySelectorAll('div[id*=-dropdown]')];

dropdowns.forEach((elem) => {
  const id = elem.getAttribute('id');
  const field = elem.querySelector('.field');
  const dropdown = elem.querySelector('.dropdown');
  const input = elem.querySelector('input[type="text"]');
  
  const checkDropdownClose = (evt) => {
    toggle(evt, false)
  }

  const toggle = (evt, open = true) => {
    $(dropdown).data('open', !$(dropdown).data('open'));
    
    if (!$(dropdown).data('open')) {
      document.removeEventListener('click', checkDropdownClose);
      if (evt.target !== input) {
        $(dropdown).fadeOut();
      } else {
        $(dropdown).slideDown();
      }
    } else {
      setTimeout(() => document.addEventListener('click', checkDropdownClose), 100);
      if ($(dropdown).data('searchable')) {
        $(dropdown).closest('.dropdown-box').find('input[type="text"]').focus();
      }
      $(dropdown).slideDown();
    }
  };
  
  elem.addEventListener('click', toggle);
});

function discoverItems() {


    $(".input-numeric").each((k, el) => {
        $(el).on("input", function (evt) {
            $(this).val(evt.target.value.replace(/[^0-9\.]+/g, ""));
            $(this).trigger("change");
        });
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
    });

    $('.datepicker-icon').on('click', function(evt) {
      $(this).closest('.datepicker-group').find('.datepicker').focus();
    });

    $('.show-password-btn').each((k, el) => {
      if(!show_password_btn.includes(el)) {
        $(el).on('click', () => {
          const input = $(el).closest('.input').find('input');
          const type = input.attr('type') === 'password' ? 'text' : 'password';
          input.attr('type', type);
        });
        show_password_btn.push(el);
      }
    
    });
}

document.addEventListener("DOMContentLoaded", function () {
    discoverItems();

    Livewire.dispatch('initDatepickers');

    Livewire.hook("morphed", function () {
        discoverItems();
    });

    Livewire.on("deliveryDates", (data) => {
        if (datepicker1) {
            datepicker1.destroy();
            datepicker1 = null;
        }
        datepicker1 = new AirDatepicker("#datepicker", {
            onRenderCell: ({ date, cellType }) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const cellDate = new Date(date);
                    cellDate.setHours(0, 0, 0, 0);

                    if (cellDate <= today) {
                        return response;
                    }

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
        if (datepicker2) {
            datepicker2.destroy();
            datepicker2 = null;
        }
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
                today.setHours(0, 0, 0, 0);
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const cellDate = new Date(date);
                    cellDate.setHours(0, 0, 0, 0);

                    if (cellDate <= today) {
                        return response;
                    }

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
        if (datepicker3) {
            datepicker3.destroy();
            datepicker3 = null;
        }
        datepicker3 = new AirDatepicker("#datepicker3", {
            onRenderCell: ({ date, cellType }) => {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const response = {
                    disabled: true,
                    classes: "disabled-class",
                    attrs: {
                        title: "Cell is disabled",
                    },
                };

                if (cellType === "day") {
                    const cellDate = new Date(date);
                    cellDate.setHours(0, 0, 0, 0);

                    if (cellDate <= today) {
                        return response;
                    }

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

    // Toggle cargo inputs
    $('input[name="transfer_method"]').on("change", function () {
        $('input[name="transfer_method"]').each((k, el) => {
            if ($(el).prop('checked')) {
              $(el).closest(".radio-box").find(".infoblock").slideDown();
            } else {
              $(el).closest(".radio-box").find(".infoblock").slideUp();
            }
        });
    });
    $('input[name="fields.cargo"]').on("change", function () {
        $('input[name="fields.cargo"]').each((k, el) => {
            if ($(el).prop('checked')) {
              $(el).closest(".radio-box").find(".infoblock").slideDown();
            } else {
              $(el).closest(".radio-box").find(".infoblock").slideUp();
            }
        });
    });

    // Checkbox Delivery Type
    $(".checkbox-form-group").each((k, el) => {
      const target = $(el).find('input[type="checkbox"]');
      $(target).on('change', () => {
        const infoblock = $(el).closest(".checkbox-group").find(".infoblock").slideToggle();

        // if ($(target).prop('checked')) {
        //   let complete = true;
        //   infoblock.find("input").each((k, input) => {
        //     if (!$(input).val().length) {
        //       complete = false;
        //     }
        //   });
        //   if (complete) {
        //     Livewire.dispatch('runRefresh');
        //   }
        // }
        
      });

      // const inputs = $(el).closest('.checkbox-group').find('.infoblock').find('input');
      
      // inputs.each((k, input) => {
      //   $(input).on('change', () => {
      //     let complete = true;
      //     inputs.each((k, input) => {
      //       console.log($(input).val());
            
      //       if (!Number($(input).val()) || !$(input).val().length) {
      //         complete = false;
      //       }
      //     });
      //     if (complete) {
      //       Livewire.dispatch('runRefresh');
      //     }
      //   });
      // });
    });

    // Input with helper buttons
    $(".input-helper-group").each((k, el) => {
        const target = $(el).find("input");
        const helpers = $(el).find(".inut-helper-item");
        
        helpers.on("click", function () {            
            target.val($(this).text());
            
            console.log({
                name: target.attr("name"),
                value: target.val(),
            });
            
            Livewire.dispatch("setField", {
                name: target.attr("name"),
                value: target.val(),
            });
        });
    });
    
    // Counter
    $(".counter").each((k, el) => {      
        const input = $(el).find("input");
        const plus = $(el).find(".plus");
        const minus = $(el).find(".minus");
        const group = $(el).closest(".radio-box");
        const radio = group.find('input[type="radio"]');

        plus.on("click", (evt) => {              
          Livewire.dispatch("setField", {
              name: 'palletizing_type',
              value: radio.val(),
          });
          Livewire.dispatch("setField", {
              name: 'palletizing_count',
              value: Number(input.attr('data-count')) + 1,
          });
        });

        minus.on("click", (evt) => {
          const val = Number(input.attr('data-count')) - 1;
          if (val >= 0) {
            Livewire.dispatch("setField", {
              name: 'palletizing_type',
              value: radio.val(),
            });
            Livewire.dispatch("setField", {
              name: 'palletizing_count',
              value: val,
            });
          }
        });
        
        radio.on("change", (evt) => {
          $('.additional-box').find('input[type="radio"]').each((k, el) => {
            if (!$(el).prop('checked')) {
              $(el).closest('.radio-box').find('.count').html(0);
              $(el).closest('.radio-box').find('input').attr('data-count', 0);
            }
          });
        });
    });

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
