<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Account') }}
        </h2>
    </x-slot>

    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
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
            padding: 0;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
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
            font-weight: 400;
            color: #374151;
            margin-bottom: 6px;
        }
        .required {
            color: #ef4444;
            margin-left: 2px;
        }
        .optional {
            color: #9ca3af;
            font-size: 12px;
            font-style: italic;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            transition: border-color 0.15s, box-shadow 0.15s;
            background: white;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-control.is-invalid {
            border-color: #ef4444;
        }
        .invalid-feedback {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }
        textarea.form-control {
            resize: vertical;
            min-height: 90px;
            font-family: inherit;
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
            background: white;
            border-radius: 0 3px 3px 0;
        }
        .spinner-btn {
            flex: 1;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 9px;
            color: #6b7280;
            padding: 0 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .spinner-btn:hover {
            background: #f3f4f6;
        }
        .spinner-btn:first-child {
            border-bottom: 1px solid #d1d5db;
        }
        .modal-footer {
            padding: 14px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-start;
            background: #f9fafb;
        }
        .btn-submit {
            padding: 7px 24px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            background: #8b5cf6;
            color: white;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: #7c3aed;
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="modal-overlay">
                <div class="modal-container">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h3>Add Account</h3>
                        <button type="button" class="close-btn" onclick="window.location='{{ route('accounts.index') }}'">&times;</button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <p class="form-notice">
                            The field labels marked with * are required input fields.
                        </p>

                        <form action="{{ route('accounts.store') }}" method="POST" id="accountForm">
                            @csrf

                            <!-- Account No -->
                            <div class="form-group">
                                <label class="form-label">
                                    Account No <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="account_no" 
                                       class="form-control @error('account_no') is-invalid @enderror"
                                       value="{{ old('account_no', $accountNo ?? '') }}" 
                                       required>
                                @error('account_no')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="form-group">
                                <label class="form-label">
                                    Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Branch -->
                            <div class="form-group">
                                <label class="form-label">
                                    Branch <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" 
                                       name="branch" 
                                       class="form-control @error('branch') is-invalid @enderror"
                                       value="{{ old('branch') }}" 
                                       placeholder="Enter branch name">
                                @error('branch')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Swift Code -->
                            <div class="form-group">
                                <label class="form-label">
                                    Swift Code <span class="optional">(Optional)</span>
                                </label>
                                <input type="text" 
                                       name="swift_code" 
                                       class="form-control @error('swift_code') is-invalid @enderror"
                                       value="{{ old('swift_code') }}" 
                                       placeholder="Enter swift code">
                                @error('swift_code')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Initial Balance -->
                            <div class="form-group">
                                <label class="form-label">Initial Balance</label>
                                <div class="number-input-wrapper">
                                    <input type="number" 
                                           name="initial_balance" 
                                           class="form-control @error('initial_balance') is-invalid @enderror"
                                           value="{{ old('initial_balance', '0.00') }}" 
                                           step="0.01"
                                           style="padding-right: 45px;">
                                    <div class="number-spinner">
                                        <button type="button" class="spinner-btn" onclick="incrementValue()">▲</button>
                                        <button type="button" class="spinner-btn" onclick="decrementValue()">▼</button>
                                    </div>
                                </div>
                                @error('initial_balance')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="form-group">
                                <label class="form-label">Note</label>
                                <textarea name="note" 
                                          class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                                @error('note')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </form>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="submit" form="accountForm" class="btn-submit">submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function incrementValue() {
            const input = document.querySelector('input[name="initial_balance"]');
            const step = parseFloat(input.step) || 1;
            const currentValue = parseFloat(input.value) || 0;
            input.value = (currentValue + step).toFixed(2);
        }

        function decrementValue() {
            const input = document.querySelector('input[name="initial_balance"]');
            const step = parseFloat(input.step) || 1;
            const currentValue = parseFloat(input.value) || 0;
            input.value = Math.max(0, currentValue - step).toFixed(2);
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.location = '{{ route('accounts.index') }}';
            }
        });

        // Close modal when clicking outside
        document.querySelector('.modal-overlay').addEventListener('click', function(e) {
            if (e.target === this) {
                window.location = '{{ route('accounts.index') }}';
            }
        });
    </script>
</x-app-layout>