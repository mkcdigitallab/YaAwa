<script setup>
const props = defineProps({
 products: {
  type: Array,
  required: true,
 },
})
</script>
<template>

  
   <section class="categories-section">
    <div class="section-heading">
        <div>
            <p class="overline">
                À CHAQUE ENVIE, SON RAYON
            </p>

            <h2>
                De quoi se régaler
            </h2>
        </div>
    </div>

    <div class="categories">
        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            class="category"
            :style="{ '--category-color': category.color }"
            :aria-pressed="filters.category === String(category.id)"
            @click="filters.category = String(category.id)"
            >
            <img
                :src="category.image"
                :alt="''"
                width="56"
                height="48"
            />

            <span>{{ category.name }}</span>
        </button>
    </div>
    <button
    type="button"
    class="category"
    :aria-pressed="filters.category === 'all'"
    @click="filters.category = 'all'"
>
    Toutes les catégories
</button>
</section>
</template>



<style>

</style>