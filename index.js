document.addEventListener('DOMContentLoaded', () => {
    // تحديد العناصر الأساسية
    const langBtn = document.querySelector('.lang-btn');
    const langDropdown = document.querySelector('.lang-dropdown');
    const langOptions = document.querySelectorAll('.lang-option');
    const langText = document.querySelector('.lang-text');
    const html = document.documentElement; // عنصر <html> لتغيير اتجاه الصفحة

    let translations = {}; // لتخزين الترجمات

    // وظيفة لتحميل ملف الترجمة
    async function fetchTranslations() {
        try {
            const response = await fetch('translation.json');
            translations = await response.json();
            // بعد التحميل، طبق اللغة الافتراضية (أو المحفوظة في localStorage)
            const savedLang = localStorage.getItem('lang') || 'en';
            setLanguage(savedLang);
        } catch (error) {
            console.error('Error fetching translations:', error);
        }
    }

    // وظيفة لتغيير اللغة
    function setLanguage(lang) {
        if (!translations[lang]) return;

        // تغيير اتجاه الصفحة (ltr/rtl)
        if (lang === 'ar') {
            html.setAttribute('dir', 'rtl');
            html.lang = 'ar';
        } else {
            html.setAttribute('dir', 'ltr');
            html.lang = 'en';
        }
        
        // تحديث نص زر اللغة
        langText.textContent = lang === 'en' ? 'English' : 'العربية';

        // تحديث النصوص في الصفحة
        document.querySelectorAll('[data-i18n]').forEach(element => {
            const key = element.getAttribute('data-i18n');
            if (translations[lang][key]) {
                // التعامل مع النص داخل عنصر <span> لتجنب حذف التنسيق (highlight)
                if (key === 'hero_title') {
                    const originalSpan = element.querySelector('.highlight');
                    if (originalSpan) {
                        const newText = translations[lang][key];
                        // تقسيم النص عند الكلمة المراد تنسيقها وإعادة بناء العنصر
                        const parts = newText.split(' ');
                        const lastWord = parts.pop();
                        element.innerHTML = `${parts.join(' ')} <span class="highlight">${lastWord}</span>`;
                    }
                } else {
                    element.textContent = translations[lang][key];
                }
            }
        });

        // حفظ اللغة في localStorage
        localStorage.setItem('lang', lang);
    }

    // عرض/إخفاء قائمة اللغات عند الضغط على الزر
    langBtn.addEventListener('click', (event) => {
        langDropdown.classList.toggle('show');
        event.stopPropagation(); // منع إغلاق القائمة عند النقر داخلها
    });

    // الاستماع للنقرات على خيارات اللغة
    langOptions.forEach(option => {
        option.addEventListener('click', (event) => {
            event.preventDefault();
            const newLang = event.target.getAttribute('data-lang');
            setLanguage(newLang);
            langDropdown.classList.remove('show'); // إغلاق القائمة بعد الاختيار
        });
    });

    // إغلاق قائمة اللغات عند النقر في أي مكان آخر في الصفحة
    document.addEventListener('click', (event) => {
        if (!langDropdown.contains(event.target) && !langBtn.contains(event.target)) {
            langDropdown.classList.remove('show');
        }
    });

    // بدء العملية بتحميل الترجمات
    fetchTranslations();
});