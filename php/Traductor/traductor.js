// Variable para guardar los textos originales
const originales = new Map();
// Variable para guardar las traducciones en caché
const cacheTraducciones = new Map();

window.solicitarTraduccion = async function(lang) {
    const elementos = document.querySelectorAll('[data-translate]');
    
    // --- 1. VOLVER A ESPAÑOL (usa originales) ---
    if (lang === 'es') {
        elementos.forEach(el => {
            if (originales.has(el)) {
                // Usamos innerHTML para no perder etiquetas como <b>
                el.innerHTML = originales.get(el);
            }
        });
        document.documentElement.lang = 'es';
        return;
    }

    // --- 2. USAR CACHÉ SI YA LO TENEMOS ---
    if (cacheTraducciones.has(lang)) {
        const traducciones = cacheTraducciones.get(lang);
        elementos.forEach((el, index) => {
            if (traducciones[index]) {
                el.innerHTML = traducciones[index];
            }
        });
        document.documentElement.lang = lang;
        return;
    }

    // --- 3. SI NO, TRADUCIR (LÓGICA NUEVA) ---
    document.body.style.cursor = 'wait';

    // Guardar textos originales si no los tenemos
    elementos.forEach(el => {
        if (!originales.has(el)) {
            originales.set(el, el.innerHTML);
        }
    });

    // Crear un array de todos los textos a traducir
    const textosParaTraducir = Array.from(elementos).map(el => originales.get(el));

    try {
        // Hacemos UNA SOLA llamada fetch con TODOS los textos
        const respuesta = await fetch('/php/Traductor/traductor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                texts: textosParaTraducir, // Enviamos un array "texts" (plural)
                to: lang,
                from: 'es'
            })
        });

        if (!respuesta.ok) {
            // El error 429 entrará aquí
            console.error('Error de red o API:', respuesta.status, respuesta.statusText);
            throw new Error(`Error ${respuesta.status}`);
        }
        
        const traduccion = await respuesta.json();
        
        if (traduccion.ok && Array.isArray(traduccion.data)) {
            // Aplicamos las traducciones una por una
            elementos.forEach((el, index) => {
                if (traduccion.data[index]) {
                    el.innerHTML = traduccion.data[index];
                }
            });
            // Guardamos en caché
            cacheTraducciones.set(lang, traduccion.data);
            document.documentElement.lang = lang;
        } else {
            throw new Error('La respuesta del servidor no fue un array válido');
        }

    } catch (error) {
        console.error('Error al traducir:', error);
        alert('No se pudo traducir la página. Inténtalo de nuevo más tarde.');
        // Revertir a español si falla
        solicitarTraduccion('es');
    } finally {
        document.body.style.cursor = 'default';
    }
};