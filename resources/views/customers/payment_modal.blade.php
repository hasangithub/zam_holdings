<div class="modal fade"
     id="paymentModal"
     tabindex="-1">

    <div class="modal-dialog">

        <form method="POST"
              action="{{ route('customers.payment.store', $customer->id) }}">

            @csrf

            <input type="hidden"
                   name="customer_id"
                   value="{{ $customer->id }}">

            <div class="modal-content">

                <div class="modal-header">
                    <h5>Add Payment</h5>
                    <button type="button"
                            class="close"
                            data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label>Payment Date</label>

                        <input type="date"
                               name="payment_date"
                               value="{{ date('Y-m-d') }}"
                               class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Amount</label>

                        <input type="number"
                               step="0.01"
                               name="amount"
                               class="form-control"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>

                        <textarea name="remarks"
                                  class="form-control"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-success">
                        Save Payment
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>