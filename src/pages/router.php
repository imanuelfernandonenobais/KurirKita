<?php

/**
 * Daftar halaman. Nama pada URL tidak pernah digunakan sebagai path file.
 * Pemeriksaan role sudah dilakukan oleh index.php sebelum fungsi dipanggil.
 */
function render_page(string $page): void
{
    switch ($page) {
        case 'login':
            page_login();
            return;
        case 'tracking':
            page_tracking();
            return;
        case 'dashboard':
            if (current_role() === 'courier') {
                page_courier_dashboard();
            } else {
                page_dashboard();
            }
            return;
        case 'shipments':
            page_shipments();
            return;
        case 'shipment-form':
            page_shipment_form();
            return;
        case 'receipt':
            page_receipt();
            return;
        case 'shipment':
            page_shipment();
            return;
        case 'customers':
            page_customers();
            return;
        case 'couriers':
            page_couriers();
            return;
        case 'depots':
            page_depots();
            return;
        case 'users':
            page_users();
            return;
        case 'reports':
            page_reports();
            return;
        case 'courier-tasks':
            page_courier_tasks();
            return;
        case 'courier-scan':
            page_courier_scan();
            return;
        case 'courier-history':
            page_courier_history();
            return;
        case 'profile':
            page_profile();
            return;
        default:
            http_response_code(404);
            echo '404 - Halaman tidak ditemukan.';
    }
}
