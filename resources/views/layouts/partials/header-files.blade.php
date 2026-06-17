<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
    (function() {
        const savedTheme = localStorage.getItem('wp-theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<title>{{getSetting('site_title')}} | {{ucwords(str_replace('-',' ',$activeLink ?? getSetting('site_title')))}} </title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="base-url" content="{{url('/')}}">
<!-- Favicon -->
<link rel="shortcut icon" href="{{getFavicon()}}">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="{{asset('assets/backend/css/bootstrap.min.css')}}">
<!-- Fontawesome CSS -->
<link rel="stylesheet" href="{{asset('assets/backend/plugins/fontawesome/css/fontawesome.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/backend/plugins/fontawesome/css/all.min.css')}}">
<!-- Datatables CSS -->
<link rel="stylesheet" href="{{asset('assets/backend/plugins/datatables/datatables.min.css')}}">
<!-- Select2 Css -->
<link rel="stylesheet" href="{{asset('assets/backend/plugins/select2/css/select2.min.css')}}">
<!-- Text Editor Css-->
<link rel="stylesheet" href="{{asset('assets/backend/plugins/richtexteditor/rte_theme_default.css')}}">

<!-- Loader CSS -->
<link rel="stylesheet" href="{{asset('assets/backend/css/loader.css')}}">
<!-- Main CSS -->
<link rel="stylesheet" href="{{asset('assets/backend/css/style.css')}}">
<link rel='stylesheet' href="{{asset('assets/backend/plugins/fullcalendar/style.css')}}">
<link rel='stylesheet' href="{{asset('assets/backend/css/dropzone.min.css')}}">

<!-- Richtexteditor JS -->
<script src="{{asset('assets/backend/plugins/richtexteditor/rte.js')}}"></script>
<script src="{{asset('assets/backend/plugins/richtexteditor/plugins/all_plugins.js')}}"></script>
<script>
    // Dynamic data-translate attribute injection
    function addTranslateAttributes(element) {
        if (element.nodeType === Node.ELEMENT_NODE && !['SCRIPT', 'STYLE', 'IFRAME'].includes(element.tagName)) {
            if (element.childNodes.length === 1 && element.childNodes[0].nodeType === Node.TEXT_NODE) {
                element.setAttribute('data-translate', 'true');
            } else {
                element.childNodes.forEach(addTranslateAttributes);
            }
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        addTranslateAttributes(document.body);
    });
</script>
<script>
    
    async function translateText(text, targetLanguage) {
        const url = `/translate?text=${encodeURIComponent(text)}&targetLanguage=${targetLanguage}`;
        try {
            const response = await fetch(url);
            const data = await response.json();
            return data.translatedText;
        } catch (error) {
            console.error('Translation error:', error);
            return text;
        }
    }

    async function translatePage(targetLanguage) {
        // Select only <p>, <h1>-<h6>, and <span> elements with the data-translate attribute
        const elements = document.querySelectorAll('a[data-translate],label[data-translate],p[data-translate], h1[data-translate], h2[data-translate], h3[data-translate], h4[data-translate], h5[data-translate], h6[data-translate], span[data-translate]');
        
        const texts = Array.from(elements)
            .map(el => el.innerText ? el.innerText.trim() : '')
            .filter(text => text !== '');

        if (texts.length > 0) {
            const translatedTexts = await translateText(texts, targetLanguage);
            const translatedArray = translatedTexts ? translatedTexts.split(',') : [];
            if (translatedArray.length === elements.length) {
                elements.forEach((element, index) => {
                    element.innerText = translatedArray[index].trim();
                    element.innerText = translatedArray[index] ? translatedArray[index].trim() : "Translation missing";
                });
            } else {
                console.warn("Mismatch between translated texts and elements!", { translatedArray, elements });
                elements.forEach((element, index) => {
                    element.innerText = translatedArray[index] ? translatedArray[index].trim() : "Translation missing";
                });
            }
        }
    }

    // Language persistence
    const userLanguage = localStorage.getItem('userLanguage') || 'en';
    translatePage(userLanguage);

    // Language switcher
    function setLanguage(language) {
        localStorage.setItem('userLanguage', language);
        translatePage(language);
    }
</script>

<!-- 2026 UI/UX Theme Overrides -->
<link rel="stylesheet" href="{{asset('assets/backend/css/modern-theme.css')}}">

@stack('css')

@vite(['resources/css/app.css'])
