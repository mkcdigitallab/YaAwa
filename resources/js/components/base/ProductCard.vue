<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  product: {
    type: Object,
    required: true,
    validator: (product) => {
      if (!product.name) return false                  
      if (product.price <= 0) return false            
      if (product.stock < 0) return false              
      if (product.salePrice && product.salePrice >= product.price) return false 
      return true                                     
    },
  },
})

// const settings = defineProps({
//   settings:Object
// })

const styleColor = computed(()=>{
    if(props.product.stock === 0) return 'red';
    if(props.product.stock > 5) return 'green';
    return 'orange';
    })

const calculePourcentage = (product)=> {
  return Math.round(((product.price - product.salePrice) / product.price) * 100)

}    
  console.table('Produit reçu :', props.product);

</script>

<template>
  <div>
  <article class="product-card">
       <div class="product-media">
        <Link :href="`/product/${product.id}`" aria-label="Voir Riz brisé parfumé">
         <img :alt="product.name" height="240" loading="lazy" :src="product.image" width="360"/>
        </Link>
        <span class="promo-tag" v-show="product.salePrice">
         Promo {{ calculePourcentage(product) }} %
        </span>
       </div>
       <div class="product-body">
        <p class="overline">
         {{ product.origin }}
        </p>
        <h3>
         <Link :href="`/product/${product.id}`">
          {{ product.name }}
         </Link>
        </h3>
        <p class="muted small">
         {{ product.name }} {{ product.unit }} · {{ product.stock }} disponible(s)
        </p>
        <span class="stock" :style="{color: styleColor}">
         <span aria-hidden="true">
         </span>
         {{ product.stock === 0 ? 'Rupture de stock' : product.stock > 5 ? 'Disponible' : 'Stock faible' }}
        </span>
        <div class="product-bottom">
         <div>
            <del v-if="product.salePrice">{{ product.price }}</del>
            
          <strong>
           {{ product.salePrice ? product.salePrice : product.price }} FCFA
          </strong>
         </div>
         <button :aria-label="`Ajouter ${product.name} au panier`" :disabled="product.stock === 0" class="add-button" data-action="ajouter-panier" type="button">
          <svg aria-hidden="true" class="icon" fill="none" height="20" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" viewbox="0 0 24 24" width="20">
           <path d="M12 5v14M5 12h14">
           </path>
          </svg>
         </button>
        </div>
       </div>
      </article>
</div>
</template>



<style scoped>
.product-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}
</style>