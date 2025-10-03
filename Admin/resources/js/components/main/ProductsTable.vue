<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { PaginationItem, Product } from "@/types";
import { onMounted, ref } from "vue";
import { toast } from "vue-sonner";
import Pagination from "./Pagination.vue";


const products = ref<Product[]>([]);
const perPage = ref(10);
const total = ref(0);
const lastPage = ref(1);
const links = ref<PaginationItem[]>([]);
const loading = ref(false);

async function getProducts(page: number = 1) {
    loading.value = true;

    const params = new URLSearchParams({ page: String(page), per_page: String(perPage.value) });
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
    
    perPage.value = data.meta.per_page
    total.value = data.meta.total
    lastPage.value = data.meta.last_page
    links.value = data.meta.links as PaginationItem[]
}

onMounted(() => {
    getProducts(1);
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

    <Pagination :links="links" :loading="loading" @change="getProducts"></Pagination>
</template>
