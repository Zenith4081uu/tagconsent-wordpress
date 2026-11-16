function tagconsentSync() {
    var btn = document.getElementById('tc-btn-sync');
    btn.disabled = true;
    btn.textContent = 'Syncing...';
    jQuery.post(tagconsentAdmin.ajaxUrl, {
        action: 'tagconsent_sync',
        nonce: tagconsentAdmin.nonce
    }, function(r) {
        btn.disabled = false;
        btn.textContent = 'Sync Now';
        if (r.success) {
            alert('Sync successful!');
            location.reload();
        } else {
            alert('Sync failed: ' + (r.error || 'Unknown error'));
        }
    }).fail(function() {
        btn.disabled = false;
        btn.textContent = 'Sync Now';
        alert('Network error during sync');
    });
}

function tagconsentTest() {
    var btn = document.getElementById('tc-btn-test');
    btn.disabled = true;
    btn.textContent = 'Testing...';
    jQuery.post(tagconsentAdmin.ajaxUrl, {
        action: 'tagconsent_test_connection',
        nonce: tagconsentAdmin.nonce
    }, function(r) {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
        if (r.success) {
            alert('Connection successful! TagConsent is properly configured.');
        } else {
            alert('Connection failed: ' + (r.error || 'Unknown error'));
        }
    }).fail(function() {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
        alert('Network error — cannot reach TagConsent');
    });
}
