import "./bootstrap";
import AirDatepicker from "air-datepicker";
import "air-datepicker/air-datepicker.css";
import $ from "jquery";

document.addEventListener("DOMContentLoaded", function () {
    const disableDates = ({ date, cellType }) => {
        const today = new Date();
        const response = {
            disabled: true,
            classes: "disabled-class",
            attrs: {
                title: "Cell is disabled",
            },
        };

        if (cellType === "day") {
            if (date.getMonth() < today.getMonth()) {
                return response;
            } else if (
                date.getMonth() === today.getMonth() &&
                date.getDate() <= today.getDate()
            ) {
                return response;
            }
        }
    };

    const datepicker = new AirDatepicker("#datepicker", {
        onRenderCell: disableDates,
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

    const datepicker2 = new AirDatepicker("#datepicker2", {
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
        onRenderCell: disableDates,
    });

    const datepicker3 = new AirDatepicker("#datepicker3", {
        onRenderCell: disableDates,
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
    $('input[name="transfer_method"]').on("change", function () {
        Livewire.dispatch('setField', { name: $(this).attr('name'), value: $(this).val() });
        $('input[name="transfer_method"]').each((k, el) => {
            $(el).closest(".group").find(".infoblock").slideToggle();
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
        });
    });
    // End Checkbox

    // Input with helper buttons
    $(".input-helper-group").each((k, el) => {
        const target = $(el).find("input");
        const helpers = $(el).find(".inut-helper-item");

        helpers.on("click", function () {
            target.val($(this).html());
        });
    });
    // End Input with helper buttons

    // Counter input
    $(".counter").each((k, el) => {
        const input = $(el).find("input");
        const count = $(el).find(".count");
        const plus = $(el).find(".plus");
        const minus = $(el).find(".minus");
        const group = $(el).closest(".group");
        const radio = group.find('input[type="radio"]');

        let state = input.val();
        count.html(state);

        const setSate = (val) => {
            state = val;
            count.html(state);
            input.val(state);
        };

        const hasChecked = () => radio.is(":checked");
        const clearRelated = () => {
            const key = group.data("related");
            if (key) {
                const related = $(`[data-related="${key}"]`);
                related.each((k, e) => {
                    e !== group ? $(e).find(".counter").trigger("clear") : null;
                });
            }
        };

        plus.on("click", (evt) => {
            if (!hasChecked()) {
                radio.prop("checked", true);
                clearRelated();
            }
            setSate(+state + 1);
        });

        minus.on("click", (evt) => {
            if (!hasChecked()) {
                radio.prop("checked", true);
                clearRelated();
            }

            +state - 1 > 0 ? setSate(+state - 1) : setSate(state);
        });

        radio.on("change", (evt) => clearRelated());

        $(el).on("clear", () => setSate(0));
    });
    // End Counter input

    // Distributors input
    $(".distributor-group").each((k, el) => {
        $(el)
            .find(".distributor-item")
            .each((key, elem) => {
                $(elem).on("click", function () {
                    const radio = $(this).find('input[type="radio"]');
                    radio.prop("checked", !radio.prop("checked"));
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
          if (dropdown.hasClass('hidden')) {
            // dropdown.removeClass('hidden');
            dropdown.attr('data-shopen', true);
            dropdown.slideToggle(() => {
              dropdown.removeClass('hidden')
            });
          }
        });
        input.on("focusout", () => {
          if (!dropdown.hasClass('hidden')) {
            dropdown.slideToggle(() => {
              dropdown.addClass('hidden');
              dropdown.attr('data-shopen', false);
            });
          }
        });

        if (input.data('filter')) {
          input.on("input", () =>
            input.val().length
                ? filterFields(input.val())
                : appendFields(fields)
          );
        }

        if (input.data('search')) {
          input.on('input', () => {
            Livewire.dispatch('setField', { name: 'user_focused_dropdown', value: $(el).data('dropdown') });
            setTimeout(() => {
              Livewire.dispatch('setField', { name: 'user_address_query', value: input.val() });
            }, 300);
          });
        }
    });
    // End Dropdown component
});

window.addEventListener('fieldClean', event => {
  const params = event.detail[0];
  if (Object.keys(params).includes('type') && params['type'] === 'datepicker') {
    const elem = $(`[data-datepicker="${params['name']}"]`);
    elem.val(null);
  }
  if (Object.keys(params).includes('type') && params['type'] === 'dropdown') {
    const elem = $(`[data-dropdown="${params['name']}"]`).find('input');
    elem.val(null);
  }
});

window.addEventListener('fieldUpdated', event => {
  const params = event.detail[0];
  if (Object.keys(params).includes('type') && params['type'] === 'dropdown') {
    const elem = $(`[data-dropdown="${params['name']}"]`).find('.input');
    elem.val(params['value']);
  }
})