<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Account') }}
        </h2>
    </x-slot>

    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            z-index: 9999;
            padding-top: 50px;
            overflow-y: auto;
        }
        .modal-container {
            background: white;
            border-radius: 6px;
            width: 90%;
            max-width: 620px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            margin-bottom: 50px;
        }
        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #374151;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #9ca3af;
            cursor: pointer;
        }
        .close-btn:hover {
            color: #6b7280;
        }
        .modal-body {
            padding: 20px;
        }
        .form-notice {
            font-size: 13px;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            color: #374151;
            margin-bottom: 6px;
        }
        .required {
            color: #ef4444;
        }
        .optional {
            color: #9ca3af;
            font-size: 12px;
            font-style: italic;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
            outline: none;
        }
        .form-control.is-invalid {
            border-color: #ef4444;
        }
        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
        }
        .number-input-wrapper {
            position: relative;
        }
        .number-spinner {
            position: absolute;
            right: 2px;
            top: 2px;
            bottom: 2px;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #d1d5db;
        }
        .spinner-btn {
            flex: 1;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 9px;
        }
        .spinner-btn:hover {
            background: #f3f4f6;
        }
        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        .btn-submit {
            padding: 7px 24px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            background: #8b5cf6;
            color: white;
            cursor: pointer;
        }
        .btn-submit:hover {
            background: #7c3aed;
        }
    </style>

    <div class="py-6">
        <div class="modal-overlay">
            <div class="modal-container">
                <!-- Header -->
                <div class="modal-header">
                    <h3>Edit Account</h3>
                    <button class="close-btn" onclick="window.location='{{ route('accounts.index') }}'">&times;</button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <p class="form-notice">The field labels marked with * are required.</p>

                    <form action="{{ route('accounts.update', $account->id) }}" method="POST" id="accountForm">
                        @csrf
                        @method('PUT')

                        <!-- Account No -->
                        <div class="form-group">
                            <label class="form-label">Account No <span class="required">*</span></label>
                            <input type="text" name="account_no"
                                   class="form-control @error('account_no') is-invalid @enderror"
                                   value="{{ old('account_no', $account->account_no) }}" required>
                            @error('account_no')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $account->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <!-- Branch -->
                        <div class="form-group">
                            <label class="form-label">Branch <span class="optional">(Optional)</span></label>
                            <input type="text" name="branch"
                                   class="form-control @error('branch') is-invalid @enderror"
                                   value="{{ old('branch', $account->branch) }}">
                            @error('branch')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <!-- Swift Code -->
                        <div class="form-group">
                            <label class="form-label">Swift Code <span class="optional">(Optional)</span></label>
                            <input type="text" name="swift_code"
                                   class="form-control @error('swift_code') is-invalid @enderror"
                                   value="{{ old('swift_code', $account->swift_code) }}">
                            @error('swift_code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <!-- Initial Balance -->
                        <div class="form-group">
                            <label class="form-label">Initial Balance</label>
                            <div class="number-input-wrapper">
                                <input type="number" name="initial_balance"
                                       class="form-control @error('initial_balance') is-invalid @enderror"
                                       value="{{ old('initial_balance', $account->initial_balance) }}"
                                       step="0.01" style="padding-right:45px;">
                                <div class="number-spinner">
                                    <button type="button" class="spinner-btn" onclick="inc()">▲</button>
                                    <button type="button" class="spinner-btn" onclick="dec()">▼</button>
                                </div>
                            </div>
                            @error('initial_balance')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <!-- Note -->
                        <div class="form-group">
                            <label class="form-label">Note</label>
                            <textarea name="note"
                                      class="form-control @error('note') is-invalid @enderror">{{ old('note', $account->note) }}</textarea>
                            @error('note')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="submit" form="accountForm" class="btn-submit">Update</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function inc() {
            const i = document.querySelector('[name="initial_balance"]');
            i.value = (parseFloat(i.value || 0) + 0.01).toFixed(2);
        }
        function dec() {
            const i = document.querySelector('[name="initial_balance"]');
            i.value = Math.max(0, parseFloat(i.value || 0) - 0.01).toFixed(2);
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') window.location='{{ route('accounts.index') }}';
        });

        document.querySelector('.modal-overlay')
            .addEventListener('click', e => {
                if (e.target === e.currentTarget)
                    window.location='{{ route('accounts.index') }}';
            });
    </script>
</x-app-layout>
