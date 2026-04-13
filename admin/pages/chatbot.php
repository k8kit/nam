<?php
/**
 * Admin Chatbot Settings Page
 * Place at: admin/pages/chatbot.php
 * Add link in admin/dashboard.php sidebar
 */
displayAlert();

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_chatbot'])) {
    $api_key = trim($_POST['anthropic_api_key'] ?? '');

    // Mask check — don't overwrite with the masked display value
    if (strpos($api_key, '••••') === false) {
        $stmt = $conn->prepare(
            "INSERT INTO chatbot_settings (setting_key, setting_value)
             VALUES ('anthropic_api_key', ?)
             ON DUPLICATE KEY UPDATE setting_value = ?"
        );
        $stmt->bind_param('ss', $api_key, $api_key);
        if ($stmt->execute()) {
            setAlert('Chatbot API key saved successfully.', 'success');
        } else {
            setAlert('Failed to save: ' . $stmt->error, 'danger');
        }
        $stmt->close();
        header('Location: ../dashboard.php?page=chatbot');
        exit();
    }
}

// Load current key
$current_key = '';
$res = $conn->query("SELECT setting_value FROM chatbot_settings WHERE setting_key = 'anthropic_api_key' LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $current_key = $row['setting_value'];
}
// Mask the key for display
$masked_key = '';
if (!empty($current_key)) {
    $len = strlen($current_key);
    $masked_key = substr($current_key, 0, 7) . str_repeat('•', max(0, $len - 11)) . substr($current_key, -4);
}

// Quick stats
$total_msgs = 0; // placeholder — could add a logs table later
?>

<div style="max-width:720px;">

    <div style="margin-bottom:2rem;">
        <h2 style="margin:0 0 .3rem;">Chatbot Settings</h2>
        <p style="margin:0;color:var(--text-light);font-size:.9rem;">
            Configure the AI chatbot that appears on your website. It uses the Anthropic Claude API and is restricted to only answer questions about NAM Builders.
        </p>
    </div>

    <!-- Status card -->
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="width:48px;height:48px;border-radius:12px;background:<?php echo !empty($current_key) ? 'rgba(40,167,69,.12)' : 'rgba(220,53,69,.10)'; ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:<?php echo !empty($current_key) ? '#28A745' : '#DC3545'; ?>;">
                <i class="fas fa-<?php echo !empty($current_key) ? 'check-circle' : 'times-circle'; ?>"></i>
            </div>
            <div>
                <div style="font-weight:800;font-size:1rem;color:var(--text-dark);">
                    Chatbot is <?php echo !empty($current_key) ? 'Active' : 'Not Configured'; ?>
                </div>
                <div style="font-size:.83rem;color:var(--text-light);">
                    <?php echo !empty($current_key) ? 'API key is set. The chatbot widget is live on your website.' : 'An Anthropic API key is required to enable the chatbot.'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- API Key form -->
    <div class="admin-card" style="margin-bottom:1.5rem;">
        <div style="font-size:.7rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-light);margin-bottom:1.2rem;">
            <i class="fas fa-key" style="color:var(--primary-color);margin-right:.4rem;"></i> Anthropic API Key
        </div>

        <form method="POST">
            <div class="form-group">
                <label style="font-weight:600;font-size:.9rem;color:var(--text-dark);">API Key</label>
                <div style="position:relative;">
                    <input type="password"
                           name="anthropic_api_key"
                           class="form-control"
                           id="apiKeyInput"
                           placeholder="sk-ant-api03-••••••••••••••••"
                           value="<?php echo htmlspecialchars($masked_key); ?>"
                           style="padding-right:3rem;font-family:monospace;font-size:.88rem;"
                           autocomplete="new-password">
                    <button type="button"
                            onclick="toggleApiKeyVisibility()"
                            style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-light);cursor:pointer;font-size:.9rem;"
                            title="Toggle visibility">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <small style="color:var(--text-light);margin-top:.4rem;display:block;">
                    Get your key from <a href="https://console.anthropic.com/settings/keys" target="_blank" rel="noopener" style="color:var(--primary-color);">console.anthropic.com</a>.
                    Keys start with <code style="background:var(--light-bg);padding:.1rem .35rem;border-radius:4px;font-size:.8rem;">sk-ant-api03-</code>
                </small>
            </div>

            <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
                <button type="submit" name="save_chatbot" class="btn-add">
                    <i class="fas fa-save"></i> Save API Key
                </button>
                <?php if (!empty($current_key)): ?>
                <button type="button" onclick="clearApiKey()" class="btn-delete" style="padding:.55rem 1.1rem;">
                    <i class="fas fa-trash"></i> Remove Key
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Info cards -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div class="admin-card" style="margin:0;">
            <div style="font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-light);margin-bottom:.8rem;">
                <i class="fas fa-robot" style="color:#007BFF;margin-right:.35rem;"></i> Model Used
            </div>
            <div style="font-weight:700;font-size:.95rem;color:var(--text-dark);">claude-haiku-4-5</div>
            <div style="font-size:.78rem;color:var(--text-light);margin-top:.2rem;">Fast, low-cost model — ideal for a chatbot</div>
        </div>
        <div class="admin-card" style="margin:0;">
            <div style="font-size:.72rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--text-light);margin-bottom:.8rem;">
                <i class="fas fa-shield-alt" style="color:#28A745;margin-right:.35rem;"></i> Topic Restriction
            </div>
            <div style="font-weight:700;font-size:.95rem;color:var(--text-dark);">NAM Builders Only</div>
            <div style="font-size:.78rem;color:var(--text-light);margin-top:.2rem;">Bot refuses off-topic questions automatically</div>
        </div>
    </div>

    <!-- Rate limit info -->
    <div class="admin-card" style="background:#FFFBEB;border-color:#FDE68A;">
        <div style="display:flex;gap:.75rem;align-items:flex-start;">
            <i class="fas fa-info-circle" style="color:#D97706;margin-top:.15rem;flex-shrink:0;"></i>
            <div style="font-size:.86rem;color:#92400E;line-height:1.7;">
                <strong>Rate Limiting:</strong> Each visitor is limited to <strong>30 messages per hour</strong> to prevent abuse.
                This is enforced server-side via PHP sessions.<br>
                <strong>Cost Tip:</strong> claude-haiku-4-5 costs approximately $0.001–0.003 per conversation. Monitor usage at <a href="https://console.anthropic.com" target="_blank" rel="noopener" style="color:#1565C0;">console.anthropic.com</a>.
            </div>
        </div>
    </div>

</div>

<!-- Form to clear key -->
<form id="clearKeyForm" method="POST" style="display:none;">
    <input type="hidden" name="anthropic_api_key" value="">
    <input type="hidden" name="save_chatbot" value="1">
</form>

<script>
function toggleApiKeyVisibility() {
    var input = document.getElementById('apiKeyInput');
    var icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
function clearApiKey() {
    if (confirm('Remove the API key? The chatbot will stop working.')) {
        document.getElementById('clearKeyForm').submit();
    }
}
</script>