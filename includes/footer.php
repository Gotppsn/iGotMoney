<?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                </main>
            </div>
        </div>
    <?php else: ?>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-auto">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?php echo date('Y'); ?> iGotMoney - Your Personal Finance Manager
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($page_scripts)): ?>
        <script>
            <?php echo $page_scripts; ?>
        </script>
    <?php endif; ?>
</body>
</html>