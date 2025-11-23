(function(){
  try {
    if (localStorage.getItem('darkMode') === null) localStorage.setItem('darkMode','true');
  }catch(e){}
})();