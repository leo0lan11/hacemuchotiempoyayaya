// Simple JS placeholder - e.g., click on thumb -> open detail
document.addEventListener('click', function(e){
  const t = e.target.closest('.thumb');
  if(t){
    window.location.href = 'pages/detail.html';
  }
});
