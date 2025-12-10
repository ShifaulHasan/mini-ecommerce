<x-app-layout>

    <h2 class="text-xl font-bold mb-4">Edit Sale</h2>

    <form action="{{ route('sales.update', $sale->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Customer</label>
            <select name="customer_id" class="form-control">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Warehouse</label>
            <select name="warehouse_id" class="form-control">
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ $sale->warehouse_id == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Sale Date</label>
            <input type="date" name="sale_date" value="{{ $sale->sale_date }}" class="form-control">
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ $sale->notes }}</textarea>
        </div>

        <button class="btn btn-primary">Update Sale</button>

    </form>

</x-app-layout>
