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
  
});