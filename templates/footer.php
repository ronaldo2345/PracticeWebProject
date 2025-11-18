</main>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-utensils"></i> Virtual Kitchen</h5>
                <p>Share and discover delicious recipes from around the world.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>/index.php" class="text-white">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php" class="text-white">Search Recipes</a></li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?= BASE_URL ?>/dashboard.php" class="text-white">Your Recipes</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Connect</h5>
                <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <hr>
        <div class="text-center">
            &copy; <?= date('Y') ?> Virtual Kitchen. All rights reserved.
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

<?php
// Clear flashed messages
if (isset($_SESSION['flash'])) {
unset($_SESSION['flash']);
}
?>
