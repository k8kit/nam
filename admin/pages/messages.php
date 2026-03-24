<?php
// Messages Management Page
$query  = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($query);
$messages    = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$total_items = count($messages);
displayAlert();

// Pagination
$per_page    = 10;
$total_pages = max(1, ceil($total_items / $per_page));
$cur_page    = max(1, min($total_pages, intval($_GET['messages_page'] ?? 1)));
$offset      = ($cur_page - 1) * $per_page;
$page_items  = array_slice($messages, $offset, $per_page);
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:.75rem;">
    <h2 style="margin:0;">
        Contact Messages
        <span style="font-size:.8rem;font-weight:600;color:var(--text-light);margin-left:.5rem;">
            (<?php echo $total_items; ?> total)
        </span>
    </h2>
    <div style="display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;">
        <!-- Status filter buttons -->
        <div style="display:flex; gap:.35rem;">
            <button class="msg-filter-btn active" data-filter="all"     onclick="filterByStatus(this, 'all')">     All     <span id="countAll"></span></button>
            <button class="msg-filter-btn"         data-filter="unread"  onclick="filterByStatus(this, 'unread')">  Unread  <span id="countUnread"></span></button>
            <button class="msg-filter-btn"         data-filter="read"    onclick="filterByStatus(this, 'read')">    Read    <span id="countRead"></span></button>
            <button class="msg-filter-btn"         data-filter="replied" onclick="filterByStatus(this, 'replied')"> Replied <span id="countReplied"></span></button>
        </div>
        <!-- Search -->
        <div class="msg-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="msgSearchInput" placeholder="Search messages…" oninput="applyMessageFilters(this.value)">
        </div>
    </div>
</div>

<div class="admin-card">
    <?php if (!empty($messages)): ?>
        <table class="admin-table" id="messagesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($page_items as $message):
                    if (!empty($message['is_replied']))     { $sk = 'replied'; $sl = 'Replied'; $si = 'fa-reply';  }
                    elseif (!empty($message['is_read']))    { $sk = 'read';    $sl = 'Read';    $si = 'fa-check';  }
                    else                                    { $sk = 'unread';  $sl = 'Unread';  $si = 'fa-circle'; }
                ?>
                <tr
                    data-status="<?php echo $sk; ?>"
                    data-search="<?php echo strtolower(htmlspecialchars(
                        $message['full_name'] . ' ' .
                        $message['email']     . ' ' .
                        ($message['phone']          ?? '') . ' ' .
                        ($message['service_needed'] ?? '') . ' ' .
                        $message['message']
                    )); ?>">
                    <td><?php echo sanitize($message['full_name']); ?></td>
                    <td><?php echo sanitize($message['email']); ?></td>
                    <td><?php echo sanitize($message['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo sanitize($message['service_needed'] ?? 'General'); ?></td>
                    <td><?php echo formatDate($message['created_at']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $sk; ?>">
                            <i class="fas <?php echo $si; ?>"></i> <?php echo $sl; ?>
                        </span>
                    </td>
                    <td>
                        <div class="admin-actions">
                            <button class="btn-edit"   onclick="viewMessage(<?php echo $message['id']; ?>)"><i class="fas fa-eye"></i></button>
                            <button class="btn-delete" onclick="openMsgDeleteConfirm(<?php echo $message['id']; ?>, '<?php echo addslashes(sanitize($message['full_name'])); ?>')"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <tr id="msgNoResults" style="display:none;">
                    <td colspan="8" style="text-align:center; color:var(--text-light); padding:2rem;">
                        <i class="fas fa-search" style="margin-right:.4rem;"></i> No messages match your search.
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="adm-pagination">
            <div class="adm-pag-info">
                Showing <strong><?php echo $offset + 1; ?>–<?php echo min($offset + $per_page, $total_items); ?></strong>
                of <strong><?php echo $total_items; ?></strong> messages
            </div>
            <div class="adm-pag-btns">
                <a href="?page=messages&messages_page=<?php echo max(1, $cur_page - 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page <= 1 ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>

                <?php
                $range = 2;
                $start = max(1, $cur_page - $range);
                $end   = min($total_pages, $cur_page + $range);

                if ($start > 1) echo '<a href="?page=messages&messages_page=1" class="adm-pag-btn">1</a>';
                if ($start > 2) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';

                for ($p = $start; $p <= $end; $p++):
                ?>
                    <a href="?page=messages&messages_page=<?php echo $p; ?>"
                       class="adm-pag-btn <?php echo $p === $cur_page ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor;

                if ($end < $total_pages - 1) echo '<span style="padding:0 .25rem;color:var(--text-light);">…</span>';
                if ($end < $total_pages)     echo '<a href="?page=messages&messages_page=' . $total_pages . '" class="adm-pag-btn">' . $total_pages . '</a>';
                ?>

                <a href="?page=messages&messages_page=<?php echo min($total_pages, $cur_page + 1); ?>"
                   class="adm-pag-btn <?php echo $cur_page >= $total_pages ? 'disabled' : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <p style="text-align:center; color:var(--text-light); padding:2rem;">
            <i class="fas fa-inbox"></i> No messages yet.
        </p>
    <?php endif; ?>
</div>

<!-- ── Message Detail Modal ── -->
<div class="msg-modal-overlay" id="messageModal">
    <div class="msg-modal-box">
        <div class="mm-header">
            <button class="mm-close-btn" onclick="closeMessageModal()">&times;</button>
            <div class="mm-header-inner">
                <div class="mm-avatar" id="mmAvatar">?</div>
                <div class="mm-header-text">
                    <h3 id="mmName">—</h3>
                    <p  id="mmEmailSub">—</p>
                </div>
            </div>
        </div>
        <div class="mm-meta-row" id="mmMetaRow"></div>
        <div class="mm-body">
            <div class="mm-field-label">Message</div>
            <div class="mm-message-box" id="mmMessageBox">—</div>
            <div class="mm-reply-section">
                <div class="mm-reply-header">
                    <i class="fas fa-reply" style="color:#1565C0;font-size:.95rem;"></i>
                    <h4>Reply to Sender</h4>
                    <span class="mm-reply-to-tag" id="mmReplyToTag">—</span>
                </div>
                <textarea class="mm-reply-textarea" id="mmReplyTextarea"
                          placeholder="Type your reply here…&#10;&#10;The sender will receive this message via email."></textarea>
            </div>
        </div>
        <div class="mm-footer">
            <button class="mm-btn mm-btn-close" onclick="closeMessageModal()">
                <i class="fas fa-times"></i> Close
            </button>
            <button class="mm-btn mm-btn-reply" id="mmSendReplyBtn" onclick="sendReply()">
                <i class="fas fa-paper-plane"></i> Send Reply
            </button>
        </div>
    </div>
</div>

<!-- ── Delete Confirmation Modal ── -->
<div class="confirm-modal-overlay" id="msgDeleteConfirmModal">
    <div class="confirm-modal-box">
        <div class="confirm-modal-header">
            <div class="confirm-modal-icon"><i class="fas fa-trash-alt"></i></div>
            <div>
                <h3>Delete Message</h3>
                <p>This action cannot be undone</p>
            </div>
        </div>
        <div class="confirm-modal-body">
            <p>Are you sure you want to delete the message from <strong id="deleteMsgName">this sender</strong>?</p>
        </div>
        <div class="confirm-modal-footer">
            <button class="confirm-btn confirm-btn-cancel" onclick="closeMsgDeleteConfirm()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button class="confirm-btn confirm-btn-delete" onclick="executeMsgDelete()">
                <i class="fas fa-trash-alt"></i> Yes, Delete
            </button>
        </div>
    </div>
</div>