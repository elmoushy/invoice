<template>
</template>

<script>
import { useInvoiceStore } from '../invoice'; 

export default {
    mounted() {


        const invoiceStore = useInvoiceStore();
        invoiceStore.$reset(); 
        this.$router.push('/create-invoice/invoice-details');
        
        // Option 2: Using plain JavaScript (uncomment if not using Vue Router)
        // window.location.href = '/create-invoice/invoice-details';
    }
}
</script>