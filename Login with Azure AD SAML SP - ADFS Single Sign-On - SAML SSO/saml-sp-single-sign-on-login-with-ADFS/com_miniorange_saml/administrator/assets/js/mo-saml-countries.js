/**
 * Country list and phone code dropdown for contact form.
 * Dropdown list shows "(Country name) +code"; after select shows only "+code".
 */
(function () {
    'use strict';

    var countries = [
        { name: "Afghanistan", code: "AF", dial: "93" },
        { name: "Albania", code: "AL", dial: "355" },
        { name: "Algeria", code: "DZ", dial: "213" },
        { name: "American Samoa", code: "AS", dial: "1" },
        { name: "Andorra", code: "AD", dial: "376" },
        { name: "Angola", code: "AO", dial: "244" },
        { name: "Anguilla", code: "AI", dial: "1" },
        { name: "Antigua and Barbuda", code: "AG", dial: "1" },
        { name: "Argentina", code: "AR", dial: "54" },
        { name: "Armenia", code: "AM", dial: "374" },
        { name: "Aruba", code: "AW", dial: "297" },
        { name: "Australia", code: "AU", dial: "61" },
        { name: "Austria", code: "AT", dial: "43" },
        { name: "Azerbaijan", code: "AZ", dial: "994" },
        { name: "Bahamas", code: "BS", dial: "1" },
        { name: "Bahrain", code: "BH", dial: "973" },
        { name: "Bangladesh", code: "BD", dial: "880" },
        { name: "Barbados", code: "BB", dial: "1" },
        { name: "Belarus", code: "BY", dial: "375" },
        { name: "Belgium", code: "BE", dial: "32" },
        { name: "Belize", code: "BZ", dial: "501" },
        { name: "Benin", code: "BJ", dial: "229" },
        { name: "Bermuda", code: "BM", dial: "1" },
        { name: "Bhutan", code: "BT", dial: "975" },
        { name: "Bolivia", code: "BO", dial: "591" },
        { name: "Bosnia and Herzegovina", code: "BA", dial: "387" },
        { name: "Botswana", code: "BW", dial: "267" },
        { name: "Brazil", code: "BR", dial: "55" },
        { name: "British Virgin Islands", code: "VG", dial: "1" },
        { name: "Brunei", code: "BN", dial: "673" },
        { name: "Bulgaria", code: "BG", dial: "359" },
        { name: "Burkina Faso", code: "BF", dial: "226" },
        { name: "Burundi", code: "BI", dial: "257" },
        { name: "Cambodia", code: "KH", dial: "855" },
        { name: "Cameroon", code: "CM", dial: "237" },
        { name: "Canada", code: "CA", dial: "1" },
        { name: "Cape Verde", code: "CV", dial: "238" },
        { name: "Cayman Islands", code: "KY", dial: "1" },
        { name: "Central African Republic", code: "CF", dial: "236" },
        { name: "Chad", code: "TD", dial: "235" },
        { name: "Chile", code: "CL", dial: "56" },
        { name: "China", code: "CN", dial: "86" },
        { name: "Colombia", code: "CO", dial: "57" },
        { name: "Comoros", code: "KM", dial: "269" },
        { name: "Congo", code: "CG", dial: "242" },
        { name: "Costa Rica", code: "CR", dial: "506" },
        { name: "Croatia", code: "HR", dial: "385" },
        { name: "Cuba", code: "CU", dial: "53" },
        { name: "Cyprus", code: "CY", dial: "357" },
        { name: "Czech Republic", code: "CZ", dial: "420" },
        { name: "Denmark", code: "DK", dial: "45" },
        { name: "Djibouti", code: "DJ", dial: "253" },
        { name: "Dominica", code: "DM", dial: "1" },
        { name: "Dominican Republic", code: "DO", dial: "1" },
        { name: "Ecuador", code: "EC", dial: "593" },
        { name: "Egypt", code: "EG", dial: "20" },
        { name: "El Salvador", code: "SV", dial: "503" },
        { name: "Equatorial Guinea", code: "GQ", dial: "240" },
        { name: "Eritrea", code: "ER", dial: "291" },
        { name: "Estonia", code: "EE", dial: "372" },
        { name: "Eswatini", code: "SZ", dial: "268" },
        { name: "Ethiopia", code: "ET", dial: "251" },
        { name: "Fiji", code: "FJ", dial: "679" },
        { name: "Finland", code: "FI", dial: "358" },
        { name: "France", code: "FR", dial: "33" },
        { name: "Gabon", code: "GA", dial: "241" },
        { name: "Gambia", code: "GM", dial: "220" },
        { name: "Georgia", code: "GE", dial: "995" },
        { name: "Germany", code: "DE", dial: "49" },
        { name: "Ghana", code: "GH", dial: "233" },
        { name: "Greece", code: "GR", dial: "30" },
        { name: "Greenland", code: "GL", dial: "299" },
        { name: "Grenada", code: "GD", dial: "1" },
        { name: "Guatemala", code: "GT", dial: "502" },
        { name: "Guinea", code: "GN", dial: "224" },
        { name: "Guyana", code: "GY", dial: "592" },
        { name: "Haiti", code: "HT", dial: "509" },
        { name: "Honduras", code: "HN", dial: "504" },
        { name: "Hong Kong", code: "HK", dial: "852" },
        { name: "Hungary", code: "HU", dial: "36" },
        { name: "Iceland", code: "IS", dial: "354" },
        { name: "India", code: "IN", dial: "91" },
        { name: "Indonesia", code: "ID", dial: "62" },
        { name: "Iran", code: "IR", dial: "98" },
        { name: "Iraq", code: "IQ", dial: "964" },
        { name: "Ireland", code: "IE", dial: "353" },
        { name: "Israel", code: "IL", dial: "972" },
        { name: "Italy", code: "IT", dial: "39" },
        { name: "Jamaica", code: "JM", dial: "1" },
        { name: "Japan", code: "JP", dial: "81" },
        { name: "Jordan", code: "JO", dial: "962" },
        { name: "Kazakhstan", code: "KZ", dial: "7" },
        { name: "Kenya", code: "KE", dial: "254" },
        { name: "Kuwait", code: "KW", dial: "965" },
        { name: "Latvia", code: "LV", dial: "371" },
        { name: "Lebanon", code: "LB", dial: "961" },
        { name: "Liberia", code: "LR", dial: "231" },
        { name: "Lithuania", code: "LT", dial: "370" },
        { name: "Luxembourg", code: "LU", dial: "352" },
        { name: "Malaysia", code: "MY", dial: "60" },
        { name: "Maldives", code: "MV", dial: "960" },
        { name: "Mexico", code: "MX", dial: "52" },
        { name: "Mongolia", code: "MN", dial: "976" },
        { name: "Morocco", code: "MA", dial: "212" },
        { name: "Nepal", code: "NP", dial: "977" },
        { name: "Netherlands", code: "NL", dial: "31" },
        { name: "New Zealand", code: "NZ", dial: "64" },
        { name: "Nigeria", code: "NG", dial: "234" },
        { name: "Norway", code: "NO", dial: "47" },
        { name: "Oman", code: "OM", dial: "968" },
        { name: "Pakistan", code: "PK", dial: "92" },
        { name: "Philippines", code: "PH", dial: "63" },
        { name: "Poland", code: "PL", dial: "48" },
        { name: "Portugal", code: "PT", dial: "351" },
        { name: "Qatar", code: "QA", dial: "974" },
        { name: "Romania", code: "RO", dial: "40" },
        { name: "Russia", code: "RU", dial: "7" },
        { name: "Saudi Arabia", code: "SA", dial: "966" },
        { name: "Singapore", code: "SG", dial: "65" },
        { name: "South Africa", code: "ZA", dial: "27" },
        { name: "South Korea", code: "KR", dial: "82" },
        { name: "Spain", code: "ES", dial: "34" },
        { name: "Sri Lanka", code: "LK", dial: "94" },
        { name: "Sweden", code: "SE", dial: "46" },
        { name: "Switzerland", code: "CH", dial: "41" },
        { name: "Thailand", code: "TH", dial: "66" },
        { name: "Turkey", code: "TR", dial: "90" },
        { name: "Ukraine", code: "UA", dial: "380" },
        { name: "United Arab Emirates", code: "AE", dial: "971" },
        { name: "United Kingdom", code: "GB", dial: "44" },
        { name: "United States", code: "US", dial: "1" },
        { name: "Vietnam", code: "VN", dial: "84" },
        { name: "Yemen", code: "YE", dial: "967" },
        { name: "Zimbabwe", code: "ZW", dial: "263" }
    ];

    function initPhoneCodeDropdown() {
        var wrapper = document.querySelector('.mo_saml_phone_code_wrapper');
        if (!wrapper) return;

        var hiddenInput = wrapper.querySelector('input[name="query_phone_code"]');
        var displayEl = wrapper.querySelector('.mo_saml_code_display');
        var listEl = wrapper.querySelector('.mo_saml_code_list');
        var placeholder = wrapper.getAttribute('data-placeholder') || 'Select country code';
        var initialCode = (wrapper.getAttribute('data-initial-code') || '').trim();

        if (!hiddenInput || !displayEl || !listEl) return;

        function setDisplay(text) {
            displayEl.textContent = text;
        }

        var typeBuf = '', typeTid;
        function filterBy(buf) {
            var q = buf.toLowerCase();
            [].forEach.call(listEl.querySelectorAll('.mo_saml_code_item'), function (el) {
                el.style.display = !q || el.getAttribute('data-name').toLowerCase().indexOf(q) !== -1 ? 'block' : 'none';
            });
        }
        function onKey(e) {
            if (listEl.style.display !== 'block') return;
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                typeBuf += e.key.toLowerCase();
                clearTimeout(typeTid);
                typeTid = setTimeout(function () { typeBuf = ''; }, 700);
                filterBy(typeBuf);
            }
        }
        function closeList() {
            listEl.style.display = 'none';
            typeBuf = '';
            document.removeEventListener('click', closeList);
            document.removeEventListener('keydown', onKey);
        }
        function openList() {
            typeBuf = '';
            filterBy('');
            listEl.style.display = 'block';
            setTimeout(function () {
                document.addEventListener('click', closeList);
                document.addEventListener('keydown', onKey);
            }, 0);
        }

        listEl.innerHTML = '';
        countries.forEach(function (c) {
            var dialVal = '+' + c.dial;
            var item = document.createElement('div');
            item.className = 'mo_saml_code_item';
            item.setAttribute('data-code', dialVal);
            item.setAttribute('data-name', c.name);
            item.style.padding = '6px 10px';
            item.style.cursor = 'pointer';
            item.style.whiteSpace = 'nowrap';
            item.textContent = '(' + c.name + ') ' + dialVal;
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                hiddenInput.value = dialVal;
                setDisplay(dialVal);
                closeList();
            });
            listEl.appendChild(item);
        });

        if (initialCode) {
            hiddenInput.value = initialCode;
            setDisplay(initialCode);
        } else {
            setDisplay(placeholder);
        }

        displayEl.addEventListener('click', function (e) {
            e.stopPropagation();
            if (listEl.style.display === 'none' || !listEl.style.display) {
                openList();
            } else {
                closeList();
            }
        });

        listEl.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPhoneCodeDropdown);
    } else {
        initPhoneCodeDropdown();
    }
})();
