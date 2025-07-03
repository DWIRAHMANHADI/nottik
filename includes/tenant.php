<?php
// includes/tenant.php - Kelas dan fungsi untuk branding multi-tenant
class Tenant {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }
    // Ambil data tenant berdasarkan id
    public function getById($tenantId) {
        return $this->db->fetchOne("SELECT * FROM tenants WHERE id = ?", [$tenantId]);
    }
    // Update branding tenant
    public function updateBranding($tenantId, $data) {
        return $this->db->update('tenants', $data, 'id = ?', [$tenantId]);
    }
}
?>
