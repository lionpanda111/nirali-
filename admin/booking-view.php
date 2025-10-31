<?php
// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];

// Set the page title
$page_title = 'View Booking';

// Include header
include 'includes/header.php';

try {
    // Get booking details
    $stmt = $pdo->prepare(
        "SELECT b.*, s.title as service_title, s.price as service_price, s.duration as service_duration 
         FROM bookings b 
         LEFT JOIN services s ON b.service_id = s.id 
         WHERE b.id = ?"
    );
    
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    
    if (!$booking) {
        $_SESSION['error'] = 'Booking not found.';
        header('Location: bookings.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error in booking view: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while fetching booking details.';
    header('Location: bookings.php');
    exit;
}
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Booking Details</h1>
        <div>
            <a href="booking-edit.php?id=<?php echo $booking_id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Booking
            </a>
            <a href="bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>
    
    <!-- Booking Details Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Booking #<?php echo $booking['id']; ?></h6>
                    <span class="badge bg-<?php 
                        echo [
                            'pending' => 'warning',
                            'confirmed' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ][$booking['status']] ?? 'secondary';
                    ?> text-uppercase">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Customer Information</h5>
                            <p class="mb-1">
                                <i class="fas fa-user me-2"></i> 
                                <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-envelope me-2"></i> 
                                <a href="mailto:<?php echo htmlspecialchars($booking['customer_email']); ?>">
                                    <?php echo htmlspecialchars($booking['customer_email']); ?>
                                </a>
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-phone me-2"></i> 
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $booking['customer_phone']); ?>">
                                    <?php echo htmlspecialchars($booking['customer_phone']); ?>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="font-weight-bold">Booking Information</h5>
                            <p class="mb-1">
                                <i class="far fa-calendar-alt me-2"></i> 
                                <?php 
                                    $booking_date = new DateTime($booking['booking_date']);
                                    echo $booking_date->format('l, F j, Y');
                                ?>
                            </p>
                            <p class="mb-1">
                                <i class="far fa-clock me-2"></i> 
                                <?php echo date('g:i A', strtotime($booking['booking_time'])); ?>
                                (<?php echo $booking['service_duration'] ?? 60; ?> minutes)
                            </p>
                            <p class="mb-1">
                                <i class="fas fa-tag me-2"></i> 
                                <?php echo htmlspecialchars($booking['service_title'] ?? 'N/A'); ?>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-clock me-2"></i> 
                                <?php echo !empty($booking['service_duration']) ? $booking['service_duration'] . ' minutes' : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['notes'])): ?>
                        <div class="border-top pt-3">
                            <h5 class="font-weight-bold">Customer Notes</h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-right">
                    <span class="text-muted small">
                        Created: <?php echo date('M j, Y \a\t g:i A', strtotime($booking['created_at'])); ?>
                        <?php if ($booking['created_at'] != $booking['updated_at']): ?>
                            <br>Last updated: <?php echo date('M j, Y \a\t g:i A', strtotime($booking['updated_at'])); ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <!-- Status History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php
                        // In a real application, you would fetch this from a status_history table
                        $status_history = [
                            [
                                'status' => 'pending',
                                'timestamp' => $booking['created_at'],
                                'note' => 'Booking created'
                            ],
                            [
                                'status' => $booking['status'],
                                'timestamp' => $booking['updated_at'],
                                'note' => 'Status updated to ' . $booking['status']
                            ]
                        ];
                        
                        foreach (array_reverse($status_history) as $history):
                            $status_class = [
                                'pending' => 'warning',
                                'confirmed' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ][$history['status']] ?? 'secondary';
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?php echo $status_class; ?>"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="font-weight-bold">
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($history['status']); ?>
                                            </span>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($history['timestamp'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($history['note']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success btn-block mb-2" data-bs-toggle="modal" data-bs-target="#confirmModal" data-action="confirm">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <button type="button" class="btn btn-success btn-block mb-2" data-bs-toggle="modal" data-bs-target="#completeModal" data-action="complete">
                                <i class="fas fa-check-double"></i> Mark as Completed
                            </button>
                        <?php endif; ?>
                        
                        <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                            <button type="button" class="btn btn-warning btn-block mb-2" data-bs-toggle="modal" data-bs-target="#cancelModal" data-action="cancel">
                                <i class="fas fa-times"></i> Cancel Booking
                            </button>
                        <?php endif; ?>
                        
                        <a href="#" class="btn btn-info btn-block mb-2" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                            <i class="fas fa-envelope"></i> Send Message
                        </a>
                        
                        <a href="#" class="btn btn-primary btn-block mb-2" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                            <i class="fas fa-plus"></i> Add Note
                        </a>
                        
                        <a href="booking-print.php?id=<?php echo $booking_id; ?>" target="_blank" class="btn btn-secondary btn-block mb-2">
                            <i class="fas fa-print"></i> Print Details
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Customer Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Internal Notes</h6>
                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <div class="card-body">
                    <?php
                    // In a real application, you would fetch these from a notes table
                    $notes = [
                        [
                            'note' => 'Customer requested a specific makeup artist - assigned to Sarah.',
                            'created_by' => 'Admin User',
                            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                        ]
                    ];
                    
                    if (empty($notes)): 
                    ?>
                        <p class="text-muted text-center py-3">No notes added yet.</p>
                    <?php else: ?>
                        <div class="timeline">
                            <?php foreach ($notes as $note): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                                        <small class="text-muted">
                                            <?php echo $note['created_by']; ?> - 
                                            <?php echo date('M j, Y g:i A', strtotime($note['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="booking-update.php" method="post">
                <input type="hidden" name="id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="status" value="confirmed">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to confirm this booking?</p>
                    <div class="mb-3">
                        <label for="confirmNotes" class="form-label">Add a note (optional):</label>
                        <textarea class="form-control" id="confirmNotes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendEmail" name="send_email" checked>
                        <label class="form-check-label" for="sendEmail">
                            Send confirmation email to customer
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="booking-update.php" method="post">
                <input type="hidden" name="id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="status" value="completed">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="completeModalLabel">Mark as Completed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Mark this booking as completed?</p>
                    <div class="mb-3">
                        <label for="completeNotes" class="form-label">Add a note (optional):</label>
                        <textarea class="form-control" id="completeNotes" name="notes" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendCompletionEmail" name="send_email" checked>
                        <label class="form-check-label" for="sendCompletionEmail">
                            Send completion email to customer
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="booking-update.php" method="post">
                <input type="hidden" name="id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="status" value="cancelled">
                
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="cancelModalLabel">Cancel Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking?</p>
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Reason for cancellation (optional):</label>
                        <textarea class="form-control" id="cancelReason" name="notes" rows="3" required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="sendCancellationEmail" name="send_email" checked>
                        <label class="form-check-label" for="sendCancellationEmail">
                            Send cancellation email to customer
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Cancel Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="booking-add-note.php" method="post">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">Add Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Note</label>
                        <textarea class="form-control" id="noteContent" name="note" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Message Modal -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="send-message.php" method="post">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($booking['customer_email']); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="sendMessageModalLabel">Send Message to Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="messageSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="messageSubject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="messageContent" class="form-label">Message</label>
                        <textarea class="form-control" id="messageContent" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0 0 0 1rem;
    border-left: 2px solid #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -1.8rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    background: #dee2e6;
    z-index: 1;
    top: 0.25rem;
}

.timeline-content {
    padding-left: 0.5rem;
}

/* Status specific colors */
.bg-warning { background-color: #ffc107 !important; }
.bg-primary { background-color: #0d6efd !important; }
.bg-success { background-color: #198754 !important; }
.bg-danger { background-color: #dc3545 !important; }
.bg-secondary { background-color: #6c757d !important; }
.bg-info { background-color: #0dcaf0 !important; }
</style>

<?php
// Include footer
include 'includes/footer.php';
?>
