<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Payment System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .invoice-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
            display: none;
        }
        .invoice-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .invoice-table th {
            background-color: #f8f9fa;
        }
        .invoice-total {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn-print {
            background-color: #6c757d;
            color: white;
        }
        .btn-download {
            background-color: #28a745;
            color: white;
        }
        .modal-content {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .checkout-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .checkout-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Canteen Order System</h3>
                    </div>
                    <div class="card-body">
                        <p class="lead">Click the button below to make a payment and generate an invoice.</p>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            <i class="fas fa-shopping-cart me-2"></i>Checkout
                        </button>
                    </div>
                </div>
                
                <!-- Invoice Container -->
                <div class="invoice-container" id="invoiceContainer">
                    <div class="invoice-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h2>INVOICE</h2>
                                <p class="mb-1"><strong>Invoice #:</strong> <span id="invoiceNumber">INV-001</span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="invoiceDate"></span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h4>College Canteen</h4>
                                <p class="mb-1">ABC University Campus</p>
                                <p class="mb-1">New Delhi, 110001</p>
                                <p class="mb-1">Phone: +91 9876543210</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Bill To:</h5>
                            <p class="mb-1"><strong id="studentName">Student Name</strong></p>
                            <p class="mb-1" id="studentId">Student ID: S12345</p>
                            <p class="mb-1" id="studentCourse">Course: B.Tech Computer Science</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5>Payment Details:</h5>
                            <p class="mb-1"><strong>Payment Method:</strong> Cash</p>
                            <p class="mb-1"><strong>Payment Date:</strong> <span id="paymentDate"></span></p>
                            <p class="mb-1"><strong>Transaction ID:</strong> <span id="transactionId">TXN-001</span></p>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price (₹)</th>
                                    <th class="text-end">Amount (₹)</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceItems">
                                <!-- Invoice items will be populated here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong id="invoiceSubtotal">0.00</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>GST (5%):</strong></td>
                                    <td class="text-end"><strong id="invoiceGst">0.00</strong></td>
                                </tr>
                                <tr class="invoice-total">
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong id="invoiceTotal">0.00</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <p class="mb-1"><strong>Payment Terms:</strong></p>
                            <p class="mb-1">Payment is due within 15 days. Please make checks payable to College Canteen.</p>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <p>Thank you for your business!</p>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button class="btn btn-print me-2" id="printInvoice">
                                <i class="fas fa-print me-1"></i>Print Invoice
                            </button>
                            <button class="btn btn-download" id="downloadInvoice">
                                <i class="fas fa-download me-1"></i>Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade checkout-modal" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-3 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="checkoutModalLabel">Complete Your Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentForm" action="" method="POST">
                    <input type="hidden" name="uid" value="12345">
                    <input type="hidden" name="place_order" value="1">
                    <div class="modal-body">
                        <div id="checkoutItems">
                            <!-- Sample items for demonstration -->
                            <div class="checkout-item">
                                <span>Veg Burger</span>
                                <span>₹80.00 × 2</span>
                            </div>
                            <div class="checkout-item">
                                <span>French Fries</span>
                                <span>₹60.00 × 1</span>
                            </div>
                            <div class="checkout-item">
                                <span>Cold Coffee</span>
                                <span>₹50.00 × 2</span>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Total Amount (₹)</label>
                            <input type="number" class="form-control" id="totalAmount" name="totalAmount" value="330" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Pay Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jsPDF for PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set current date for invoice
            const now = new Date();
            document.getElementById('invoiceDate').textContent = formatDate(now);
            document.getElementById('paymentDate').textContent = formatDate(now);
            
            // Handle payment form submission
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
                modal.hide();
                
                // Generate invoice
                generateInvoice();
            });
            
            // Print invoice functionality
            document.getElementById('printInvoice').addEventListener('click', function() {
                window.print();
            });
            
            // Download PDF functionality
            document.getElementById('downloadInvoice').addEventListener('click', function() {
                downloadPDF();
            });
            
            // Format date function
            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            }
            
            // Generate invoice function
            function generateInvoice() {
                // Get checkout items
                const checkoutItems = document.getElementById('checkoutItems').children;
                
                // Populate invoice items
                const invoiceItems = document.getElementById('invoiceItems');
                invoiceItems.innerHTML = '';
                
                let subtotal = 0;
                
                for (let i = 0; i < checkoutItems.length; i++) {
                    const item = checkoutItems[i];
                    const itemText = item.children[0].textContent;
                    const itemDetails = item.children[1].textContent;
                    
                    // Parse item details (assuming format: "₹price × quantity")
                    const priceMatch = itemDetails.match(/₹(\d+\.?\d*)/);
                    const quantityMatch = itemDetails.match(/×\s*(\d+)/);
                    
                    if (priceMatch && quantityMatch) {
                        const price = parseFloat(priceMatch[1]);
                        const quantity = parseInt(quantityMatch[1]);
                        const amount = price * quantity;
                        subtotal += amount;
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${i + 1}</td>
                            <td>${itemText}</td>
                            <td class="text-center">${quantity}</td>
                            <td class="text-end">${price.toFixed(2)}</td>
                            <td class="text-end">${amount.toFixed(2)}</td>
                        `;
                        invoiceItems.appendChild(row);
                    }
                }
                
                // Calculate GST and total
                const gst = subtotal * 0.05;
                const total = subtotal + gst;
                
                // Update invoice totals
                document.getElementById('invoiceSubtotal').textContent = subtotal.toFixed(2);
                document.getElementById('invoiceGst').textContent = gst.toFixed(2);
                document.getElementById('invoiceTotal').textContent = total.toFixed(2);
                
                // Generate random invoice and transaction numbers
                const invoiceNumber = 'INV-' + Math.floor(1000 + Math.random() * 9000);
                const transactionId = 'TXN-' + Math.floor(10000 + Math.random() * 90000);
                
                document.getElementById('invoiceNumber').textContent = invoiceNumber;
                document.getElementById('transactionId').textContent = transactionId;
                
                // Show the invoice
                document.getElementById('invoiceContainer').style.display = 'block';
                
                // Scroll to invoice
                document.getElementById('invoiceContainer').scrollIntoView({ behavior: 'smooth' });
            }
            
            // Download PDF function
            function downloadPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                
                // Get invoice content
                const invoiceElement = document.getElementById('invoiceContainer');
                
                // Use html2canvas to capture the invoice as an image
                html2canvas(invoiceElement).then(canvas => {
                    const imgData = canvas.toDataURL('image/png');
                    const imgWidth = doc.internal.pageSize.getWidth();
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;
                    
                    doc.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
                    doc.save('invoice-' + document.getElementById('invoiceNumber').textContent + '.pdf');
                });
            }
        });
    </script>
</body>
</html>