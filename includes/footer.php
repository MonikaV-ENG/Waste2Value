</main>
<footer class="site-footer">
  <div class="container footer-wrap">
    <div><strong>Waste2Value</strong><p>Give useful materials a second life.</p></div>
    <div class="footer-note">DBMS Mini Project · Built with PHP, MySQL, HTML and CSS</div>
  </div>
</footer>
<script>
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (event) {
    if (!confirm(el.getAttribute('data-confirm'))) event.preventDefault();
  });
});
</script>
</body>
</html>
