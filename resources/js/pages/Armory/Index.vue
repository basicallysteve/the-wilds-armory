<template>
<div class="container">
    <h1 class="text-center mb-4">Armory</h1>
    <SelectButton
        v-model="filters.rank"
        :options="[
            { label: 'Low Rank', value: 'low' },
            { label: 'High Rank', value: 'high' },
            // { label: 'Master Rank', value: 'master' }
        ]"
        option-label="label"
        option-value="value"
        class="mb-4"
    ></SelectButton>
    <DataTable
        filter-display="row"
        :value="armors"
        :global-filter-fields="['kind', 'slots', 'rarity']"
    >
    <Column field="name" header="Name"></Column>
    <Column field="kind" header="Kind">
        <template #body="slotProps">
            {{ slotProps.data.kind.charAt(0).toUpperCase() + slotProps.data.kind.slice(1) }}
        </template>
        <template #filter="{ filterModel, filterCallBack}">
            <Select
                v-model="filters[`filter[kind]`]"
                :options="[
                    { label: 'Head', value: 'head' },
                    { label: 'Chest', value: 'chest' },
                    { label: 'Arms', value: 'arms' },
                    { label: 'Waist', value: 'waist' },
                    { label: 'Legs', value: 'legs' }
                ]"
                optionLabel="label"
                optionValue="value"
            ></Select>
        </template>
    </Column>
    <Column field="defense.base" header="Base Defense"></Column>
    <Column field="defense.max" header="Max Defense"></Column>
    <Column field="weakestAgainst" header="Weakest To">
        <template #body="slotProps">
            {{ weakestAgainst(slotProps.data.resistances) }}
        </template>
    </Column>
    <Column field="strongestAgainst" header="Strongest Against">
        <template #body="slotProps">
            {{ strongestAgainst(slotProps.data.resistances) }}
        </template>
    </Column>
    <Column field="slots" header="Slots">
        <template #body="slotProps">
            {{ slotProps.data.slots.length > 0 ? slotProps.data.slots.length : '--' }}
        </template>
    </Column>
    <Column field="rarity" header="Rarity">
        <template #body="slotProps">
            {{ slotProps.data.rarity }}
        </template>
        <template #filter="{ filterModel, filterCallBack}">
            <InputNumber
                v-model="filters[`filter[rarity]`]"
                mode="decimal"
                :min="1"
                :max="8"
                :step="1"
                inputId="rarityFilter"
            ></InputNumber>
        </template>
    </Column>
    <Column field="rank" header="Rank"></Column>
</DataTable>
</div>
</template>
<script lang="ts" setup>
import { type PropType, ref, watch } from 'vue';
import { router } from "@inertiajs/vue3";
interface ArmorResistance {
    fire: number;
    water: number;
    thunder: number;
    ice: number;
    dragon: number;
}

interface ArmorDefense {
    base: number;
    max: number;
}   
interface Armor {
    id: number;
    kind: 'head' | 'chest' | 'arms' | 'waist' | 'legs';
    resistances: ArmorResistance;
    defense: ArmorDefense;
    slots: number[];
    rarity: number;
    rank: 'low' | 'high' | 'master';
}

const props = defineProps({
    armors: {
        type: Array as PropType<Armor[]>,
        default: () => [],
        required: true
    }
})

function strongestAgainst(armors: ArmorResistance): string {
    const elements = Object.keys(armors) as (keyof ArmorResistance)[];
    let strongestElement = elements[0];
    let maxResistance = armors[strongestElement];

    for (const element of elements) {
        if (armors[element] > maxResistance) {
            maxResistance = armors[element];
            strongestElement = element;
        }
    }
    return strongestElement;
}

function weakestAgainst(armorResistance: ArmorResistance): string {
    const elements = Object.keys(armorResistance) as (keyof ArmorResistance)[];
    let weakestElement = elements[0];
    let minResistance = armorResistance[weakestElement];

    for (const element of elements) {
        if (armorResistance[element] < minResistance) {
            minResistance = armorResistance[element];
            weakestElement = element;
        }
    }
    return weakestElement;
}

const filters = ref({
    rank: 'low'
});


watch(filters, (newFilters: object) => {
    if(newFilters){
        const filterObj = {};
        for(const key in newFilters) {
            if (newFilters[key] && !(key.includes('filter') || key.includes('range'))) {
                filterObj[`filter[${key}]`] = newFilters[key] ;
            }else{
                filterObj[key] = newFilters[key];
            }
        }
        router.reload({
            data: filterObj,
            preserveState: true,
            preserveScroll: true
        })
    }
}, { deep: true });


</script>