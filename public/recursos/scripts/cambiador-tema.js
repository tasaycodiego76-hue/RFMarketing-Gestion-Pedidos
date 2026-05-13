/**
 * ══════════════════════════════════════════════════════════
 * theme-switcher.js — Sistema de Cambio de Tema
 * Maneja la lógica de cambio entre tema oscuro y claro
 * ══════════════════════════════════════════════════════════
 */

class ThemeSwitcher {
    constructor() {
        this.STORAGE_KEY = 'rf-marketing-theme';
        this.DEFAULT_THEME = 'dark';
        this.THEMES = {
            DARK: 'dark',
            LIGHT: 'light'
        };
        
        this.init();
    }

    /**
     * Inicializa el sistema de temas
     */
    init() {
        this.loadTheme();
        this.setupEventListeners();
    }

    /**
     * Carga el tema guardado o usa el predeterminado
     */
    loadTheme() {
        const savedTheme = localStorage.getItem(this.STORAGE_KEY) || this.DEFAULT_THEME;
        this.setTheme(savedTheme);
    }

    /**
     * Establece el tema actual
     * @param {string} theme - 'dark' o 'light'
     */
    setTheme(theme) {
        if (!Object.values(this.THEMES).includes(theme)) {
            theme = this.DEFAULT_THEME;
        }

        // Aplicar el atributo data-theme al elemento html
        document.documentElement.setAttribute('data-theme', theme);
        
        // Guardar en localStorage
        localStorage.setItem(this.STORAGE_KEY, theme);
        
        // Actualizar Bootstrap theme si es necesario
        document.documentElement.setAttribute('data-bs-theme', theme);
        
        // Disparar evento personalizado
        this.dispatchThemeChangeEvent(theme);
        
        // Actualizar ícono del botón
        this.updateThemeToggleButton(theme);
    }

    /**
     * Alterna entre tema oscuro y claro
     */
    toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || this.DEFAULT_THEME;
        const newTheme = currentTheme === this.THEMES.DARK ? this.THEMES.LIGHT : this.THEMES.DARK;
        this.setTheme(newTheme);
    }

    /**
     * Obtiene el tema actual
     * @returns {string} Tema actual ('dark' o 'light')
     */
    getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') || this.DEFAULT_THEME;
    }

    /**
     * Configura los escuchadores de eventos
     */
    setupEventListeners() {
        const themeToggleBtn = document.getElementById('theme-toggle-btn');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => this.toggleTheme());
        }

        // Responder a cambios del sistema operativo (prefers-color-scheme)
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeQuery.addEventListener('change', (e) => {
                const newTheme = e.matches ? this.THEMES.DARK : this.THEMES.LIGHT;
                // Solo aplicar si el usuario no ha establecido una preferencia manual
                if (!localStorage.getItem(this.STORAGE_KEY)) {
                    this.setTheme(newTheme);
                }
            });
        }
    }

    /**
     * Actualiza el ícono del botón de cambio de tema
     * @param {string} theme - Tema actual
     */
    updateThemeToggleButton(theme) {
        const btn = document.getElementById('theme-toggle-btn');
        if (btn) {
            const icon = btn.querySelector('i');
            if (icon) {
                // Cambiar el ícono según el tema
                if (theme === this.THEMES.DARK) {
                    icon.className = 'bi bi-sun-fill';
                    btn.setAttribute('title', 'Cambiar a tema claro');
                } else {
                    icon.className = 'bi bi-moon-stars-fill';
                    btn.setAttribute('title', 'Cambiar a tema oscuro');
                }
            }
        }
    }

    /**
     * Dispara un evento personalizado cuando cambia el tema
     * @param {string} theme - Tema actual
     */
    dispatchThemeChangeEvent(theme) {
        const event = new CustomEvent('themechange', { detail: { theme } });
        window.dispatchEvent(event);
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeSwitcher = new ThemeSwitcher();
    });
} else {
    window.themeSwitcher = new ThemeSwitcher();
}
