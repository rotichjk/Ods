
window.UI = (function(){
  const KEY='od_theme';
  function apply(t){
    if(!t){ t = localStorage.getItem(KEY) || 'light'; }
    document.documentElement.setAttribute('data-theme', t==='dark'?'dark':'');
    localStorage.setItem(KEY, t);
  }
  function toggleTheme(){
    const cur = localStorage.getItem(KEY) || 'light';
    apply(cur==='dark'?'light':'dark');
  }
  document.addEventListener('DOMContentLoaded',()=>apply());
  return { toggleTheme };
})();
