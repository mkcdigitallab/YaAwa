<script setup>
import { onMounted, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import api from '@/routes/api'

const props = defineProps({
 productId: {
  type: Number,
  required: true,
 },
 homeUrl: {
  type: String,
  required: true,
 },
});

console.log(props);

const product = ref(null)
const loading = ref(true)
const errorMessage = ref('')
let abortController
let isMounted = false

async function loadProduct(productId) {
 abortController?.abort()
 abortController = new AbortController()
 const currentController = abortController
 loading.value = true
 errorMessage.value = ''
 product.value = null

 try {
  const response = await fetch(api.products.show.url(productId), {
   headers: { Accept: 'application/json' },
   signal: currentController.signal,
  })

  if (response.status === 404) {
   errorMessage.value = 'Ce produit est introuvable ou n’est plus disponible.'
   return
  }

//   if (!response.ok) {
//    errorMessage.value = 'Impossible de charger ce produit pour le moment.'
//    return
//   }
if (response.status === 404) {
    errorMessage.value = 'Ce produit est introuvable ou n’est plus disponible.'
    return
}
  product.value = await response.json()
 } catch (error) {
  if (error.name !== 'AbortError') {
   errorMessage.value = 'Une erreur est survenue lors du chargement du produit.'
  }
 } finally {
  if (abortController === currentController) {
   loading.value = false
  }
 }
}


watch(
    () => props.productId,
    (productId) => {
        loadProduct(productId)
    },
    { immediate: true }
)

onMounted(() => {
 isMounted = true
 window.scrollTo({ top: 0, behavior: 'smooth' })
})
</script>

<template>
 <main class="product-detail-page" :aria-busy="loading">
  <div class="product-detail-shell">
  <Link :href="homeUrl" class="back-to-shop">← Retour à la boutique</Link>

   <p v-if="loading" class="product-detail-state" role="status">
    Chargement du produit…
   </p>

   <section v-else-if="errorMessage" class="product-detail-state" role="alert">
    <h1>Produit indisponible</h1>
    <p>{{ errorMessage }}</p>
    <Link :href="homeUrl" class="back-to-shop">Retour à la boutique</Link>
   </section>

   <article v-else-if="product" class="product-detail">
    <div class="product-detail-image">
     <img :src="product.image" :alt="product.name"/>
    </div>
    <div class="product-detail-copy">
     <p class="product-detail-category">{{ product.category.name }}</p>
     <h1>{{ product.name }}</h1>
     <p class="product-detail-origin">Origine : {{ product.origin || 'Non précisée' }}</p>
     <p class="product-detail-description">{{ product.description || 'Aucune description disponible.' }}</p>
     <div class="product-detail-price">
      <del v-if="product.salePrice">{{ product.price }} FCFA</del>
      <strong>{{ product.salePrice || product.price }} FCFA</strong>
      <span> / {{ product.unit }}</span>
     </div>
     <p :class="product.stock > 0 ? 'available' : 'unavailable'">
      {{ product.stock > 0 ? `Disponible (${product.stock} en stock)` : 'Rupture de stock' }}
     </p>
    </div>
   </article>
  </div>
 </main>
</template>

<style scoped>
.product-detail-page {
  min-height: 100vh;
  padding: 2rem 1.25rem 4rem;
  background: #f8f7f2;
  color: #252a26;
}

.product-detail-shell {
  max-width: 1120px;
  margin: 0 auto;
}

.back-to-shop {
  display: inline-block;
  margin-bottom: 1.5rem;
  color: #34634a;
  font-weight: 600;
  text-decoration: none;
}

.back-to-shop:hover {
  text-decoration: underline;
}

.product-detail {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
  gap: clamp(2rem, 6vw, 5rem);
  align-items: center;
}

.product-detail-image {
  overflow: hidden;
  min-height: 360px;
  border-radius: 8px;
  background: #ece9df;
}

.product-detail-image img {
  display: block;
  width: 100%;
  height: 100%;
  min-height: 360px;
  object-fit: cover;
}

.product-detail-category {
  margin: 0 0 0.5rem;
  color: #9a6a2a;
  font-size: 0.875rem;
  font-weight: 700;
  text-transform: uppercase;
}

.product-detail-copy h1 {
  margin: 0;
  font-size: clamp(2rem, 4vw, 3.25rem);
  line-height: 1.1;
}

.product-detail-origin,
.product-detail-description {
  color: #59635d;
  line-height: 1.7;
}

.product-detail-description {
  margin: 1.5rem 0;
}

.product-detail-price {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.product-detail-price strong {
  color: #34634a;
  font-size: 1.75rem;
}

.product-detail-price span,
.product-detail-price del {
  color: #59635d;
}

.available,
.unavailable {
  margin-top: 1rem;
  font-weight: 700;
}

.available {
  color: #34634a;
}

.unavailable {
  color: #a1392c;
}

.product-detail-state {
  padding: 3rem 1.5rem;
  border: 1px solid #e4e1d7;
  border-radius: 8px;
  background: #fff;
}

@media (max-width: 720px) {
  .product-detail {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .product-detail-image,
  .product-detail-image img {
    min-height: 260px;
    max-height: 420px;
  }
}
</style>