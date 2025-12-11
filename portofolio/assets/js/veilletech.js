// Animations: reveal on scroll, typing header, particles, save feedback
document.addEventListener('DOMContentLoaded', function(){
  // 1) Reveal on scroll
  const io = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal').forEach(el => io.observe(el));

  // 2) Typing effect
  (function typing(){
    const node = document.querySelector('.typing');
    if (!node) return;
    const text = node.dataset.text || node.textContent.trim();
    node.textContent = '';
    let i = 0;
    const speed = 28;
    const timer = setInterval(() => {
      node.textContent += text.charAt(i);
      i++;
      if (i >= text.length) clearInterval(timer);
    }, speed);
  })();

  // 3) Particles background (light)
  (function particles(){
    const root = document.getElementById('particles-root');
    if (!root) return;
    const colors = ['#38bdf8','#7c3aed','#60a5fa','#34d399'];
    for(let i=0;i<14;i++){
      const p = document.createElement('div');
      p.className = 'particle';
      const size = 8 + Math.random()*40;
      p.style.width = p.style.height = size + 'px';
      p.style.left = (Math.random()*100) + '%';
      p.style.bottom = (-40 - Math.random()*60) + 'px';
      p.style.background = colors[Math.floor(Math.random()*colors.length)];
      p.style.animationDuration = (8 + Math.random()*18) + 's';
      p.style.opacity = 0.04 + Math.random()*0.12;
      p.style.transform = 'translateY(0) scale('+ (0.6 + Math.random()*1.2) +')';
      root.appendChild(p);
    }
  })();

  // 4) Save notes: localStorage + animated toast
  const ta = document.getElementById('notes');
  const saveBtn = document.getElementById('saveNotes');
  const clearBtn = document.getElementById('clearNotes');
  const toast = document.getElementById('toast');
  const key = 'veille_ia_notes_v1';
  try { const v = localStorage.getItem(key); if(v && ta) ta.value = v; } catch(e){}

  function showToast(msg){
    toast.textContent = msg;
    toast.classList.add('show');
    setTimeout(()=> toast.classList.remove('show'), 2600);
  }

  if (saveBtn && ta){
    saveBtn.addEventListener('click', function(){
      try {
        localStorage.setItem(key, ta.value);
        // petit effet sur le bouton
        saveBtn.animate([{ transform:'scale(1)' }, { transform:'scale(0.98)' }, { transform:'scale(1)' }], { duration:220 });
        showToast('Notes enregistrées localement');
      } catch(e){
        showToast('Impossible de sauvegarder');
      }
    });
  }
  if (clearBtn && ta){
    clearBtn.addEventListener('click', function(){
      if (!confirm('Effacer les notes ?')) return;
      ta.value = '';
      try { localStorage.removeItem(key); } catch(e){}
      showToast('Notes effacées');
    });
  }

  // accessibility: pressing Escape closes any transient UI (not many here)
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
      toast.classList.remove('show');
    }
  });
});