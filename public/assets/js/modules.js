/**
 * Sistema de carregamento de módulos JavaScript
 * Carrega scripts de forma assíncrona e organizada
 */

const ModuleLoader = {
    modules: [
        '/assets/js/main.js',
        '/assets/js/sidebar.js'
    ],

    loadedModules: new Set(),

    async loadModule(src) {
        if (this.loadedModules.has(src)) {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => {
                this.loadedModules.add(src);
                console.log(`✅ Módulo carregado: ${src}`);
                resolve();
            };
            script.onerror = () => {
                console.error(`❌ Falha ao carregar: ${src}`);
                reject(new Error(`Falha ao carregar: ${src}`));
            };
            document.head.appendChild(script);
        });
    },

    async loadAll() {
        console.log('🚀 Iniciando carregamento de módulos...');
        try {
            // Carregar main.js primeiro
            await this.loadModule('/assets/js/main.js');

            // Depois carregar sidebar.js
            await this.loadModule('/assets/js/sidebar.js');

            console.log('✅ Todos os módulos carregados com sucesso');
        } catch (error) {
            console.error('❌ Erro ao carregar módulos:', error);
        }
    }
};

// Carregar módulos quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM carregado, iniciando módulos...');
    ModuleLoader.loadAll();
});