<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold text-dark mb-0">Add Category</h2>
    </x-slot>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" class="form-control" required>
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                    @error('description')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Save Category</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Back</a>

            </form>

        </div>
    </div>

</x-app-layout>
