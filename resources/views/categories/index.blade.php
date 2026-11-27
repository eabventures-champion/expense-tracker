@extends('layouts.app')

@section('kicker', 'Categories')
@section('page_title', 'Expense Categories, Custom Colors & Subcategories')

@section('content')
<!-- FULL-WIDTH CONFIGURED CATEGORIES & SUBCATEGORIES CARD -->
<section class="section-block theme-ivory">
    <div class="section-header categories-card-header">
        @php
            $expenseCatCount = $categories->where('type', 'expense')->count();
            $incomeCatCount = $categories->where('type', 'income')->count();
            $totalSubcatCount = $categories->sum(fn($c) => $c->subcategories->count());
        @endphp
        <div class="categories-header-info" style="min-width: 0; flex: 1;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                <h2 class="section-title categories-main-title" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span>Configured Expense &amp; Income Categories</span>
                    <span class="mono" style="font-size: 11px; font-weight: 800; padding: 2px 9px; border-radius: 9999px; background-color: var(--plum); color: #FFFFFF;">
                        {{ $categories->count() }}
                    </span>
                </h2>
            </div>
            <div class="categories-meta-pills" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 8px;">
                <span class="mono" style="font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 9999px; background-color: var(--coral-soft); color: var(--coral-dark);">
                    {{ $expenseCatCount }} Expense
                </span>
                <span class="mono" style="font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 9999px; background-color: var(--emerald-soft); color: var(--emerald-dark);">
                    {{ $incomeCatCount }} Income
                </span>
                <span class="mono" style="font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 9999px; background-color: var(--ivory); border: 1px solid var(--ivory-border); color: var(--plum);">
                    {{ $totalSubcatCount }} {{ \Illuminate\Support\Str::plural('Subcategory', $totalSubcatCount) }}
                </span>
                <span style="font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 9999px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); color: var(--text-muted);">
                    ↓ Ranked by Spend
                </span>
            </div>
        </div>
        <div class="categories-header-actions" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-emerald btn-sm" onclick="openCategoryModal()">+ Create Custom Category</button>
            <button type="button" class="btn btn-plum btn-sm" onclick="openSubcategoryModal()">+ Add Subcategory</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Subcategories</th>
                    <th style="text-align: right;">Recorded Spend</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                    <tr>
                        <td style="white-space: nowrap;">
                            <div>
                                <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                    <span class="cat-swatch" style="background-color: {{ $cat->color }};"></span>
                                    {{ $cat->name }}
                                </span>
                            </div>
                            <div style="margin-top: 5px; padding-left: 4px;">
                                <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background-color: {{ $cat->type === 'income' ? 'var(--emerald-soft)' : 'var(--ivory)' }}; color: {{ $cat->type === 'income' ? 'var(--emerald-dark)' : 'var(--text-muted)' }}; text-transform: uppercase;">
                                    {{ $cat->type }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @forelse($cat->subcategories as $sub)
                                    <span style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; display: inline-flex; align-items: center; gap: 6px;">
                                        <span>{{ $sub->name }}</span>
                                        <button type="button"
                                                title="Edit subcategory '{{ $sub->name }}'"
                                                onclick="openEditSubcategoryModal({{ $sub->id }}, {{ $cat->id }}, @js($sub->name))"
                                                style="border:none;background:none;color:var(--blue);cursor:pointer;font-size:11px;font-weight:700;line-height:1;padding:0 2px;">
                                            ✎
                                        </button>
                                        <form action="{{ route('subcategories.destroy', $sub) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remove subcategory?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Remove subcategory" style="border:none;background:none;color:var(--coral-dark);cursor:pointer;font-weight:700;line-height:1;">×</button>
                                        </form>
                                    </span>
                                @empty
                                    <span style="font-size: 11.5px; color: var(--text-muted);">No subcategories</span>
                                @endforelse
                                <button type="button"
                                        class="btn btn-ivory btn-sm"
                                        style="padding: 2px 8px; font-size: 11px; border-radius: 9999px;"
                                        onclick="openSubcategoryModal({{ $cat->id }})">
                                    + Sub
                                </button>
                            </div>
                        </td>
                        <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap;">
                            {{ $user->currency }}{{ number_format((float)$cat->total_spent, 2) }}
                            <div style="font-size: 10.5px; color: var(--text-muted); font-weight: 500;">{{ $cat->tx_count }} tx</div>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <button type="button"
                                        class="btn btn-ivory btn-sm"
                                        onclick="openEditCategoryModal({{ $cat->id }}, @js($cat->name), @js($cat->type), @js($cat->color), @js($cat->description ?? ''))"
                                        title="Edit category '{{ $cat->name }}'">
                                    ✎ Edit
                                </button>
                                <form action="{{ route('categories.destroy', $cat) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete category \'{{ addslashes($cat->name) }}\'?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-coral btn-sm" title="Delete category">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL 1: CREATE CUSTOM CATEGORY -->
<div id="createCategoryModal" class="modal-backdrop" onclick="if(event.target === this) closeCategoryModal()">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Taxonomy Management</div>
                <h3 style="font-size: 16px;">Create Custom Category</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeCategoryModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('categories.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="cat_modal_name" class="form-control" placeholder="e.g., Pet Care & Veterinary" oninput="checkDuplicateCategoryName('create')" required>
                    <div id="create_cat_dup_warning" style="display: none; margin-top: 5px; font-size: 11.5px; color: var(--coral-dark); font-weight: 700;"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="expense">Expense Category</option>
                            <option value="income">Income Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accent Color</label>
                        <select name="color" id="create_cat_color" class="form-control mono" data-color-picker="true">
                            <option value="#2D9F78">Emerald Green (#2D9F78)</option>
                            <option value="#E66D55">Coral (#E66D55)</option>
                            <option value="#E5A93C">Mustard Yellow (#E5A93C)</option>
                            <option value="#4E73DF">Royal Blue (#4E73DF)</option>
                            <option value="#D96D9B">Rose Pink (#D96D9B)</option>
                            <option value="#646A7A">Slate Grey (#646A7A)</option>
                            <option value="#1E1B2E">Deep Plum (#1E1B2E)</option>
                            <option value="#0EA5E9">Sky Cyan (#0EA5E9)</option>
                            <option value="#8B5CF6">Vivid Violet (#8B5CF6)</option>
                            <option value="#F97316">Tangerine Orange (#F97316)</option>
                            <option value="#14B8A6">Teal (#14B8A6)</option>
                            <option value="#EC4899">Magenta (#EC4899)</option>
                            <option value="#6366F1">Indigo (#6366F1)</option>
                            <option value="#84CC16">Lime Green (#84CC16)</option>
                            <option value="#A855F7">Purple (#A855F7)</option>
                            <option value="#F43F5E">Crimson Rose (#F43F5E)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" placeholder="Purpose of this category">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" id="create_cat_submit_btn" class="btn btn-emerald">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: ADD SUBCATEGORY -->
<div id="addSubcategoryModal" class="modal-backdrop" onclick="if(event.target === this) closeSubcategoryModal()">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Taxonomy Management</div>
                <h3 style="font-size: 16px;">Add Subcategory</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeSubcategoryModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('subcategories.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Parent Category</label>
                    <select name="expense_category_id" id="sub_modal_parent" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subcategory Name</label>
                    <input type="text" name="name" id="sub_modal_name" class="form-control" placeholder="e.g., Bottled Water & Beverages" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeSubcategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Add Subcategory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: EDIT EXISTING CATEGORY -->
<div id="editCategoryModal" class="modal-backdrop" onclick="if(event.target === this) closeEditCategoryModal()">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Taxonomy Management</div>
                <h3 style="font-size: 16px;">Edit Category</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEditCategoryModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editCategoryForm" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="name" id="edit_cat_name" class="form-control" oninput="checkDuplicateCategoryName('edit')" required>
                    <div id="edit_cat_dup_warning" style="display: none; margin-top: 5px; font-size: 11.5px; color: var(--coral-dark); font-weight: 700;"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_cat_type" class="form-control">
                            <option value="expense">Expense Category</option>
                            <option value="income">Income Category</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accent Color</label>
                        <select name="color" id="edit_cat_color" class="form-control mono" data-color-picker="true">
                            <option value="#2D9F78">Emerald Green (#2D9F78)</option>
                            <option value="#E66D55">Coral (#E66D55)</option>
                            <option value="#E5A93C">Mustard Yellow (#E5A93C)</option>
                            <option value="#4E73DF">Royal Blue (#4E73DF)</option>
                            <option value="#D96D9B">Rose Pink (#D96D9B)</option>
                            <option value="#646A7A">Slate Grey (#646A7A)</option>
                            <option value="#1E1B2E">Deep Plum (#1E1B2E)</option>
                            <option value="#0EA5E9">Sky Cyan (#0EA5E9)</option>
                            <option value="#8B5CF6">Vivid Violet (#8B5CF6)</option>
                            <option value="#F97316">Tangerine Orange (#F97316)</option>
                            <option value="#14B8A6">Teal (#14B8A6)</option>
                            <option value="#EC4899">Magenta (#EC4899)</option>
                            <option value="#6366F1">Indigo (#6366F1)</option>
                            <option value="#84CC16">Lime Green (#84CC16)</option>
                            <option value="#A855F7">Purple (#A855F7)</option>
                            <option value="#F43F5E">Crimson Rose (#F43F5E)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="edit_cat_description" class="form-control" placeholder="Purpose of this category">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEditCategoryModal()">Cancel</button>
                    <button type="submit" id="edit_cat_submit_btn" class="btn btn-emerald">Save Category Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: EDIT EXISTING SUBCATEGORY -->
<div id="editSubcategoryModal" class="modal-backdrop" onclick="if(event.target === this) closeEditSubcategoryModal()">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Taxonomy Management</div>
                <h3 style="font-size: 16px;">Edit Subcategory</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEditSubcategoryModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editSubcategoryForm" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Parent Category</label>
                    <select name="expense_category_id" id="edit_sub_parent" class="form-control" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subcategory Name</label>
                    <input type="text" name="name" id="edit_sub_name" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEditSubcategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Save Subcategory Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $existingCategoriesCatalog = $categories->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'type' => $c->type,
            'color' => strtoupper((string) $c->color),
        ];
    })->values();
@endphp
<script>
    const existingCategoriesCatalog = @json($existingCategoriesCatalog);
    let activeEditCategoryId = null;

    function normalizeCategoryName(str) {
        return (str || '').trim().replace(/\s+/g, ' ').toLowerCase();
    }

    function checkDuplicateCategoryName(mode) {
        const inputEl = document.getElementById(mode === 'edit' ? 'edit_cat_name' : 'cat_modal_name');
        const warnEl = document.getElementById(mode === 'edit' ? 'edit_cat_dup_warning' : 'create_cat_dup_warning');
        const submitBtn = document.getElementById(mode === 'edit' ? 'edit_cat_submit_btn' : 'create_cat_submit_btn');
        if (!inputEl || !warnEl || !submitBtn) return;

        const candidate = normalizeCategoryName(inputEl.value);
        if (!candidate) {
            warnEl.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            return;
        }

        const match = existingCategoriesCatalog.find(function (cat) {
            if (mode === 'edit' && activeEditCategoryId && Number(cat.id) === Number(activeEditCategoryId)) {
                return false;
            }
            return normalizeCategoryName(cat.name) === candidate;
        });

        if (match) {
            warnEl.textContent = '⚠ Category "' + match.name + '" (' + match.type.toUpperCase() + ') already exists. Duplicate category names are not allowed.';
            warnEl.style.display = 'block';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
        } else {
            warnEl.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        }
    }

    function openCategoryModal() {
        const createColorSelect = document.getElementById('create_cat_color');
        if (createColorSelect) {
            const usedColors = new Set(existingCategoriesCatalog.map(c => (c.color || '').toUpperCase()));
            const options = Array.from(createColorSelect.options).map(o => o.value.toUpperCase());
            const nextUnused = options.find(hex => !usedColors.has(hex)) || options[existingCategoriesCatalog.length % options.length];
            if (nextUnused) {
                createColorSelect.value = nextUnused;
                createColorSelect.dispatchEvent(new CustomEvent('color-picker:refresh'));
            }
        }
        document.getElementById('createCategoryModal').classList.add('open');
        checkDuplicateCategoryName('create');
        setTimeout(() => document.getElementById('cat_modal_name')?.focus(), 50);
    }

    function closeCategoryModal() {
        document.getElementById('createCategoryModal').classList.remove('open');
    }

    function openEditCategoryModal(id, name, type, color, description) {
        activeEditCategoryId = id;
        const form = document.getElementById('editCategoryForm');
        if (form) {
            form.action = '/categories/' + id;
        }
        document.getElementById('edit_cat_name').value = name || '';
        document.getElementById('edit_cat_type').value = type || 'expense';

        const colorSelect = document.getElementById('edit_cat_color');
        if (colorSelect) {
            colorSelect.value = color || '#2D9F78';
            colorSelect.dispatchEvent(new CustomEvent('color-picker:refresh'));
        }

        document.getElementById('edit_cat_description').value = description || '';
        checkDuplicateCategoryName('edit');
        document.getElementById('editCategoryModal').classList.add('open');
        setTimeout(() => document.getElementById('edit_cat_name')?.focus(), 50);
    }

    function closeEditCategoryModal() {
        document.getElementById('editCategoryModal').classList.remove('open');
    }

    function openSubcategoryModal(categoryId = null) {
        if (categoryId) {
            const parentSelect = document.getElementById('sub_modal_parent');
            if (parentSelect) parentSelect.value = categoryId;
        }
        document.getElementById('addSubcategoryModal').classList.add('open');
        setTimeout(() => document.getElementById('sub_modal_name')?.focus(), 50);
    }

    function closeSubcategoryModal() {
        document.getElementById('addSubcategoryModal').classList.remove('open');
    }

    function openEditSubcategoryModal(subId, parentCategoryId, name) {
        const form = document.getElementById('editSubcategoryForm');
        if (form) {
            form.action = '/subcategories/' + subId;
        }
        const parentSelect = document.getElementById('edit_sub_parent');
        if (parentSelect && parentCategoryId) {
            parentSelect.value = parentCategoryId;
        }
        document.getElementById('edit_sub_name').value = name || '';
        document.getElementById('editSubcategoryModal').classList.add('open');
        setTimeout(() => document.getElementById('edit_sub_name')?.focus(), 50);
    }

    function closeEditSubcategoryModal() {
        document.getElementById('editSubcategoryModal').classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCategoryModal();
            closeEditCategoryModal();
            closeSubcategoryModal();
            closeEditSubcategoryModal();
        }
    });
</script>
@endpush
