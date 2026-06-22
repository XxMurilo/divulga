document.addEventListener("DOMContentLoaded", () => {

    /* ═══════════════════════════════════════
       1. ANIMAÇÃO DOS NÚMEROS
    ══════════════════════════════════════════ */
    const contadores = document.querySelectorAll(".hero-stat-numero");

    if (contadores.length > 0) {
        const velocidade = 2500;

        const animarContador = (elemento) => {
            const alvo = elemento.getAttribute("data-target");
            if (!alvo) return;

            const numeroFinal = parseFloat(alvo);
            const sufixo = elemento.getAttribute("data-sufixo") || "";
            const incremento = numeroFinal / (velocidade / 16);
            let numeroAtual = 0;

            const atualizarNumero = () => {
                numeroAtual += incremento;
                if (numeroAtual < numeroFinal) {
                    elemento.innerText = Math.ceil(numeroAtual) + sufixo;
                    requestAnimationFrame(atualizarNumero);
                } else {
                    elemento.innerText = numeroFinal + sufixo;
                }
            };
            atualizarNumero();
        };

        contadores.forEach(contador => animarContador(contador));
    }

    /* ═══════════════════════════════════════
       2. BOTÃO VOLTAR AO TOPO
    ══════════════════════════════════════════ */
    const btnTopo       = document.getElementById("btnTopo");
    const containerSnap = document.querySelector(".container-snap");

    if (btnTopo && containerSnap) {
        containerSnap.addEventListener("scroll", () => {
            btnTopo.classList.toggle("mostrar", containerSnap.scrollTop > 300);
        });

        btnTopo.addEventListener("click", () => {
            containerSnap.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    /* ═══════════════════════════════════════
       3. NAVEGAÇÃO POR DOTS
    ══════════════════════════════════════════ */
    const dots    = document.querySelectorAll(".dot");
    const janelas = document.querySelectorAll(".janela-contexto");

    if (dots.length && containerSnap && janelas.length) {
        const atualizarDotAtivo = () => {
            const alturaJanela = containerSnap.clientHeight;
            const scrollAtual  = containerSnap.scrollTop;
            const indice = Math.round(scrollAtual / alturaJanela);

            dots.forEach((dot, i) => {
                dot.classList.toggle("ativo", i === indice);
            });
        };

        containerSnap.addEventListener("scroll", atualizarDotAtivo);

        dots.forEach((dot, i) => {
            dot.addEventListener("click", () => {
                const alturaJanela = containerSnap.clientHeight;
                containerSnap.scrollTo({ top: i * alturaJanela, behavior: "smooth" });
            });
        });
    }

    /* ═══════════════════════════════════════
       4. FADE-IN (INTERSECTION OBSERVER)
    ══════════════════════════════════════════ */
    const elementosFadeIn = document.querySelectorAll(".fade-in");

    if (elementosFadeIn.length) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visivel");
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                root: containerSnap || null,
                threshold: 0.15,
            }
        );

        elementosFadeIn.forEach(el => observer.observe(el));
    }
});
