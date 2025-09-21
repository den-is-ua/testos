<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { Product } from "@/types";
import { computed, onMounted, ref } from "vue";
import { toast } from "vue-sonner";


const products = ref<Product[]>([]);
const page = ref(1);
const perPage = ref(5);
const total = ref(0);
const lastPage = ref(1);
const loading = ref(false);

async function getProducts() {
    loading.value = true;

    const params = new URLSearchParams({ page: String(page.value), per_page: String(perPage.value) });
    const res = await fetch(`/products?${params.toString()}`, {
        headers: { 'Accept': 'application/json' },
    });

    const data = await res.json()

    if (res.status === 422) {
        console.log(data)
        toast.warning(data.message)
        return
    }

    if (res.status >= 500) {
        toast.error('Something went wrong')
        return
    }

    loading.value = false;

    products.value = data.data as Product[]
    
    page.value = data.meta.page
    perPage.value = data.meta.per_page
    total.value = data.meta.total
    lastPage.value = data.meta.last_page
}

function goTo(p: number) {
    if (p < 1 || p > lastPage.value) return;
    page.value = p;
    getProducts();
}

const pages = computed(() => {
    const arr: number[] = [];
    for (let p = 1; p <= lastPage.value; p++) arr.push(p);
    return arr;
});

onMounted(() => {
    getProducts();
});
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead class="w-[100px]">
                    SKU
                </TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Price</TableHead>
                <TableHead class="text-right">
                    Updated At
                </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-if="!loading && products.length === 0">
                <TableCell colspan="4" class="text-center py-6">No invoices found.</TableCell>
            </TableRow>

            <TableRow v-for="product in products" :key="product.id">
                <TableCell class="font-medium">
                    {{ product.sku }}
                </TableCell>
                <TableCell>{{ product.name }}</TableCell>
                <TableCell>{{ product.price }}</TableCell>
                <TableCell class="text-right">
                    {{ product.updated_at }}
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>

    <div class="flex items-center w-[100%] justify-center mt-[20px]" v-if="lastPage > 1">
        <div class="flex items-center space-x-2">
            <button class="btn" :disabled="page === 1 || loading" @click="goTo(page - 1)">
                Prev
            </button>

            <button v-for="p in pages" :key="p" :class="['btn', { 'btn-primary': p === page }]" @click="goTo(p)">
                {{ p }}
            </button>

            <button class="btn" :disabled="page === lastPage || loading" @click="goTo(page + 1)">
                Next
            </button>
        </div>
    </div>
</template>