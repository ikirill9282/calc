import './bootstrap';
import AirDatepicker from 'air-datepicker';
import 'air-datepicker/air-datepicker.css';
import $ from "jquery"

const datepicker = new AirDatepicker('#datepicker', {
});

const datepicker2 = new AirDatepicker('#datepicker2', {
});

const datepicker3 = new AirDatepicker('#datepicker3', {
});


document.addEventListener('DOMContentLoaded', function() {
  const theme_selector = document.querySelector('html');
  const theme_button = document.querySelector('#theme-button');

  const getThemeStatus = () => (window.localStorage) 
    ? window.localStorage.getItem('darkMode') === 'true'
    : null;

  const setThemeStatus = (status) => window.localStorage
    ? window.localStorage.setItem('darkMode', status)
    : null;

  const setThemeClass = () => getThemeStatus() 
      ? theme_selector.classList.add('dark') 
      : theme_selector.classList.remove('dark');

  if (getThemeStatus()) {
    theme_button.checked = true;
  } 
  setThemeClass()

  theme_button.addEventListener('change', () => {
    setThemeStatus(theme_button.checked);
    setThemeClass();
    axios.post('/api/theme', { darkMode: theme_button.checked });
  });
  
  $('input[name="cargo"]').on('change', function() {
    $('input[name="cargo"]').each((k, el) => {
      $(el).closest('.group').find('.infoblock').slideToggle();
    })
  });

  $('.checkbox-form-group').each((k, el) => {
    const target = $(el).find('input[type="checkbox"]');

    $(el).find('label').on('click', evt => evt.preventDefault());

    $(el).on('click', () => {
      $(target).prop('checked', !$(target).prop('checked'));
      $(el).closest('.checkbox-group').find('.infoblock').slideToggle();
    });
  });

  $('.input-helper-group').each((k, el) => {
    const target = $(el).find('input');
    const helpers = $(el).find('.inut-helper-item');

    helpers.on('click', function() {
      target.val($(this).html());
    });
  });

  $('.input-clear').each((k, el) => {
    $(el).on('click', () => {
      const targets = $(el).siblings('input');
      targets.each((key, input) => $(input).val(null));
    });
  });

  $('.counter').each((k, el) => {
    const input = $(el).find('input');
    const count = $(el).find('.count');
    const plus = $(el).find('.plus');
    const minus = $(el).find('.minus');
    const group = $(el).closest('.group');
    const radio = group.find('input[type="radio"]');

    let state = input.val();
    count.html(state);

    const setSate = (val) => {
      state = val;
      count.html(state);
      input.val(state);
    };

    const hasChecked = () => radio.is(':checked');
    const clearRelated = () => {
      const key = group.data('related');
      if (key) {
        const related = $(`[data-related="${key}"]`);
        related.each((k, e) => {
          e !== group ? $(e).find('.counter').trigger('clear') : null
        });
      }
    }

    plus.on('click', (evt) => {
      if (!hasChecked()) {
        radio.prop('checked', true);
        clearRelated();
      }
      setSate(+state + 1)
    });

    minus.on('click', (evt) => {
      if (!hasChecked()) {
        radio.prop('checked', true);
        clearRelated();
      }

      ((+state - 1) > 0) ? setSate(+state-1) : setSate(state);
    });

    radio.on('change', (evt) => clearRelated());

    $(el).on('clear', () => setSate(0));
  });
});