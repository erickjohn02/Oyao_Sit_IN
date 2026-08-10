    </div><!-- End of main-content -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                "pageLength": 10,
                "order": [[0, "desc"]]
            });
        });

        // Set active nav link based on current page
        $(document).ready(function() {
            const currentPage = window.location.pathname.split('/').pop();
            $('.nav-link').each(function() {
                if ($(this).attr('href') === currentPage) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>
</html> 