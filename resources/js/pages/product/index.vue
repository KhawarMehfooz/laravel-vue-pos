<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { useConfirm } from 'primevue/useconfirm';
import { debounce } from 'lodash';
import { Head, usePage, router } from '@inertiajs/vue3';
import axios from 'axios'

const confirm = useConfirm()

const { props } = usePage()
const products = ref(props.products || []);
const categories = ref(props.categories || [])
const companies = ref(props.companies || [])

const searchQuery = ref('')

const pagination = ref(props.pagination || [])
const rows = ref(pagination.value.perPage || 10)
const page = ref(pagination.value.currentPage || 1)

const createProductDialogVisible = ref(false)

const formData = ref({
    name: '',
    category_id: '',
    company_id: '',
    shelf_number: '',
    retail_price: 0,
    purchase_price: 0,
    barcode: '',
})

const selectedProduct = ref(null)

onMounted(() => {
    searchQuery.value = props.search || '';
    // Initialize pagination from props
    if (props.pagination) {
        rows.value = props.pagination.perPage;
        page.value = props.pagination.currentPage;
    }
});

watch(() => props.search, (newSearch) => {
    searchQuery.value = newSearch || '';
});

watch(() => props.pagination, (newPagination) => {
    if (newPagination) {
        pagination.value = newPagination;
        // Only update if values are different to avoid loops
        if (page.value !== newPagination.currentPage) {
            page.value = newPagination.currentPage;
        }
        if (rows.value !== newPagination.perPage) {
            rows.value = newPagination.perPage;
        }
    }
}, { deep: true });

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

const onSearchChange = debounce(() => {
    fetchProducts();
}, 500);

const openCreateProductModal = () => {
    // Clear the formData (reset to empty)
    formData.value = {
        name: '',
        category_id: '',
        company_id: '',
        shelf_number: '',
        purchase_price: 0,
        retail_price: 0,
        barcode: '',
    };
    selectedProduct.value = null;
    createProductDialogVisible.value = true;
};

const openEditProductModal = (product) => {
    formData.value = {
        name: product.name,
        shelf_number: product.shelf_number || '',
        category_id: product.category_id || '',
        company_id: product.company_id || '',
        purchase_price: product.purchase_price || 0,
        retail_price: product.retail_price || 0,
        barcode: product.barcode || 0,
    };
    selectedProduct.value = product;
    createProductDialogVisible.value = true;
};


const fetchProducts = (paginationState = { page: page.value, rows: rows.value }) => {
    router.get('/products', {
        page: paginationState.page,
        rows: paginationState.rows,
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            products.value = page.props.products;
            pagination.value = page.props.pagination;
        }
    });
};

const onPageChange = (event) => {
    // Update the current page
    page.value = event.page + 1;  // event.page starts from 0, so we add 1
    fetchProducts({ page: page.value, rows: rows.value });
};

const onRowsPerPageChange = (event) => {
    // Update the rows per page and reset to the first page
    rows.value = event;
    page.value = 1;  // Reset to the first page when rows change
    fetchProducts({ page: page.value, rows: rows.value });
};

const generateRandomBarcode = () => {
    const timestamp = Date.now().toString();
    const randomDigits = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return String(timestamp.slice(-10) + randomDigits);
};

const saveProduct = () => {
    if (!formData.value.name.trim() || !formData.value.category_id) {
        alert('Product name and category is required');
        return;
    }

    // Important: Ensure numeric values are converted properly
    const productData = {
        name: formData.value.name,
        category_id: formData.value.category_id || null,
        company_id: formData.value.company_id || null,
        shelf_number: formData.value.shelf_number?.trim() || null,
        purchase_price: Number(formData.value.purchase_price) || 0,
        retail_price: Number(formData.value.retail_price) || 0,
        barcode: formData.value.barcode ? String(formData.value.barcode) : generateRandomBarcode(),
    };

    console.log("Product Data: ", productData)

    // Capture current pagination state
    const currentPaginationState = {
        page: page.value,
        rows: rows.value
    };

    // Close dialog first to prevent any issues with form state
    createProductDialogVisible.value = false;

    if (selectedProduct.value) {
        // Update existing product
        router.put(`/products/${selectedProduct.value.id}`, productData, {
            onSuccess: () => {
                // Reset selectedProduct after successful update
                selectedProduct.value = null;
                // Refetch with current pagination state
                fetchProducts(currentPaginationState);
                resetForm();
            }
        });
    } else {
        // Create new product
        router.post('/products', productData, {
            onSuccess: () => {
                resetForm();
                // Refetch with current pagination state
                fetchProducts(currentPaginationState);
            }
        });
    }
};

// Helper function to reset the form after success
const resetForm = () => {
    formData.value = {
        name: '',
        category_id: '',
        company_id: '',
        shelf_number: '',
        purchase_price: 0,
        retail_price: 0,
        barcode: '',
    };
};

const confirmDelete = (product) => {
    confirm.require({
        message: `Are you sure you want to delete ${product.name} product?`,
        header: 'Delete Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            // Capture the current pagination state
            const currentPaginationState = {
                page: page.value,
                rows: rows.value
            };

            router.delete(`/products/${product.id}`, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    fetchProducts(currentPaginationState);
                }
            });
        },
        position: 'top'
    });
};

const fetchCategories = debounce(async (query) => {
    try {
        const response = await axios.get('/categories/search', {
            params: { search: query }
        })
        categories.value = response.data
    } catch (error) {
        console.error('Failed to load categories:', error)
    }
}, 300) // 300ms debounce

const onCategoryFilter = (event) => {
    fetchCategories(event.value)
}

const fetchCompanies = debounce(async (query) => {
    try {
        const response = await axios.get('/companies/search', {
            params: { search: query }
        })
        companies.value = response.data
    } catch (error) {
        console.error('Failed to load companies:', error)
    }
}, 300) // 300ms debounce

const onCompanyFilter = (event) => {
    fetchCompanies(event.value)
}
</script>

<template>

    <Head title="Products" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="p-4">
            <DataTable :value="products" show-gridlines paginator :lazy="true" :rows="pagination.perPage"
                :first="(pagination.currentPage - 1) * pagination.perPage" :totalRecords="pagination.total"
                :rowsPerPageOptions="[10, 50, 100]" @page="onPageChange" @update:rows="onRowsPerPageChange"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                currentPageReportTemplate="{first} to {last} of {totalRecords}" size="small">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Products</h2>
                        <div class="flex items-center gap-4">
                            <Button icon="pi pi-plus" size="small" label="Add New" @click="openCreateProductModal" />
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="searchQuery" size="small" placeholder="Search..."
                                    @input="onSearchChange" />
                            </IconField>
                        </div>
                    </div>
                </template>
                <Column field="name" header="Product Name" />
                <Column field="category.name" header="Category" />
                <Column field="company.name" header="Company" />
                <Column field="purchase_price" header="Purchase Price" />
                <Column field="retail_price" header="Retail Price" />
                <Column field="barcode" header="Barcode" />

                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex items-center gap-2">
                            <Button icon="pi pi-pencil" outlined size="small" severity="info"
                                @click="openEditProductModal(slotProps.data)" />
                            <Button icon="pi pi-trash" outlined size="small" severity="danger"
                                @click="confirmDelete(slotProps.data)" />
                        </div>
                    </template>
                </Column>
                <template #empty>No products Found...</template>
            </DataTable>

            <!-- Create Product Dialog -->
            <Dialog v-model:visible="createProductDialogVisible" modal
                :header="selectedProduct ? 'Edit Product' : 'Create Product'" :style="{ width: '40rem' }"
                position="top">
                <form @submit.prevent="saveProduct">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="productName">Product Name</label>
                            <InputText v-model="formData.name" required size="small" class="w-full" id="productName"
                                autocomplete="off" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="productCategory">Category</label>
                            <Select id="productCategory" required v-model="formData.category_id" size="small"
                                :options="categories" optionLabel="name" optionValue="id" filter
                                @filter="onCategoryFilter" placeholder="Select a Category" class="w-full" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="company_id">Company</label>
                            <Select id="company_id" v-model="formData.company_id" size="small" :options="companies"
                                filter @filter="onCompanyFilter" optionLabel="name" optionValue="id"
                                placeholder="Select a Company" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="shelf_number">Shelf Number</label>
                            <InputText v-model="formData.shelf_number" required size="small" class="w-full"
                                id="shelf_number" autocomplete="off" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="purchase_price">Purchase Price</label>
                            <InputNumber v-model="formData.purchase_price" required size="small" :min="0" class="w-full"
                                id="purchase_price" autocomplete="off" :useGrouping="false" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="retail_price">Retail Price</label>
                            <InputNumber v-model="formData.retail_price" required size="small"
                                :min="formData.purchase_price" class="w-full" id="retail_price" autocomplete="off"
                                :useGrouping="false" />
                        </div>
                        <div class="flex flex-col gap-2 mb-2 w-full">
                            <label for="barcode">Barcode</label>
                            <InputText type="number" v-model="formData.barcode" size="small" :min="0" class="w-full"
                                id="barcode" autocomplete="off" :useGrouping="false" />
                        </div>
                    </div>


                    <hr class="my-4">
                    <div class="flex items-center justify-end gap-4">
                        <Button size="small" label="Cancel" severity="secondary"
                            @click="createProductDialogVisible = false" />
                        <Button type="submit" size="small" label="Save" />
                    </div>
                </form>
            </Dialog>

            <ConfirmDialog />
        </div>
    </AppLayout>
</template>