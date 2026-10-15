# Proposal: Improve Search in WooCommerce Product Quick Edit and Bulk Edit

## Summary

This proposal suggests improving search and filtering in the WooCommerce product administration interface:

1. Add search fields to category, brand, tag, and other taxonomy checklists.
2. Add search to the list of products selected for bulk editing.

Both features would make daily catalog management easier without changing product data or altering the existing editing workflows.

## Problem

WooCommerce product and taxonomy lists can become very large over time. When these lists contain hundreds or thousands of items, finding a specific product or term requires manual scrolling through long content.

### Taxonomy checklists

Category, brand, tag, and custom taxonomy checklists can contain long lists and deeply nested hierarchies. Without an integrated search field, finding the term that needs to be reviewed or selected is unnecessarily difficult.

This is especially noticeable with:

- Categories with several levels of subcategories.
- Stores with many brands or tags.
- Taxonomies registered by WooCommerce extensions.
- Quick editing an individual product.
- Bulk editing multiple products.

### Products selected for bulk edit

Bulk Edit displays selected products in its own list. When many products are selected, reviewing the list or confirming whether a specific product is included requires manual scrolling.

The list should allow administrators to filter selected products by name without changing the actual selection or interfering with the native remove controls.

## Proposed Features

### 1. Taxonomy checklist search

Add a search field above each taxonomy checklist displayed by WooCommerce in Quick Edit and Bulk Edit.

The search should:

- Filter terms in real time with a short debounce.
- Ignore case differences.
- Handle accents and localized characters correctly.
- Keep the parents of matching terms visible.
- Keep relevant child terms visible when appropriate.
- Work with hierarchical and non-hierarchical taxonomies.
- Display a clear empty state when there are no matches.
- Provide a way to clear the search.
- Preserve existing checked values.

The implementation should operate on the taxonomies already rendered by the product editor instead of relying on a hard-coded list of taxonomy names. This would support taxonomies added by extensions.

### 2. Search for selected products

Add a search field above the list of products selected for Bulk Edit.

The search should:

- Filter selected products by name.
- Temporarily hide products that do not match.
- Keep every product selected in the form.
- Preserve the native controls used to remove products.
- Display a clear empty state when there are no matches.
- Restore all products when the field is cleared.
- Continue working when WordPress rebuilds the bulk-edit row.

This filter should be local and should not require an AJAX request or a new catalog-wide query.

## Why This Belongs in WooCommerce Core

### Common administrative workflow

Searching for products and terms is a basic catalog-management task. It is not a specialized marketing or development feature and does not depend on a particular store configuration.

### Better experience for large catalogs

Manually navigating long lists takes time and increases the risk of selecting the wrong term. A visible filter helps administrators find the required item without leaving the edit form.

### Consistent behavior

A native implementation would provide the same experience across WooCommerce stores instead of relying on small plugins with different interfaces and compatibility levels.

### Low implementation risk

These improvements are limited to the admin interface. They do not require changes to product storage, checkout, orders, or public templates.

### Extension compatibility

The search can operate on the elements already rendered in the page. Extensions that register brands or other product taxonomies could benefit without maintaining a separate integration or hard-coded taxonomy list.

## Suggested User Experience

### Quick Edit and Bulk Edit: taxonomies

1. The administrator opens Quick Edit or Bulk Edit.
2. Each taxonomy checklist displays a search field.
3. The administrator types part of a term name.
4. The list shows matching terms and the relevant hierarchical relationships.
5. The administrator selects or reviews the required term.
6. Clearing the search restores the complete checklist.

### Bulk Edit: selected products

1. The administrator selects several products from the product list.
2. The administrator opens Bulk Edit.
3. The administrator types part of a product name in the new filter.
4. The list displays only matching products.
5. The actual selection remains unchanged.
6. Clearing the search restores all selected products.

## Accessibility Requirements

- Each search field must have an accessible label.
- Clear-search buttons must be keyboard accessible.
- Empty states must be announced through an appropriate status message.
- Focus should remain predictable when a filter is cleared.
- The interface must not rely on color alone to communicate results.
- All text must be translatable.

## Performance and Security Considerations

- Filtering should operate on elements already rendered in the page.
- A debounce should avoid unnecessary work while typing.
- Filtering selected products must not query the entire catalog again.
- Searching must not modify product data.
- Existing WooCommerce permissions and security controls must remain unchanged.
- Delegated events should support edit rows rebuilt dynamically by WordPress.

## Scope

This proposal covers:

- Taxonomy search in Quick Edit.
- Taxonomy search in Bulk Edit.
- Search within the products selected for Bulk Edit.
- Hierarchical, non-hierarchical, and custom product taxonomies.
- Empty states, clear actions, accessibility, and localization.

## Prototype Evidence

A working prototype has been implemented as an independent admin plugin. It demonstrates:

- Searchable taxonomy checklists in Quick Edit and Bulk Edit.
- Search within the list of products selected for Bulk Edit.
- Preservation of relevant parents and children in hierarchical taxonomies.
- Empty states when a search has no matches.
- Clearing the search without changing selected values.
- Delegated events that continue working when WordPress rebuilds edit rows.

The prototype can help maintainers review the interaction and edge cases before selecting the final native WooCommerce implementation.

## Screenshot Placeholders

> **Screenshot 1: Taxonomy search in Quick Edit**  
> Replace this block with an image showing the search field working inside Quick Edit.

> **Screenshot 2: Taxonomy search in Bulk Edit**  
> Replace this block with an image showing the search field working with categories, brands, or tags during Bulk Edit.

> **Screenshot 3: Selected product search**  
> Replace this block with an image showing the filter applied to the list of selected products.

> **Screenshot 4: Empty state and clear action**  
> Replace this block with an image showing the no-results message and the restored list after clearing the search.

## Acceptance Criteria

- Taxonomy checklists provide a search field in Quick Edit and Bulk Edit.
- Search works with categories, brands, tags, and custom taxonomies rendered by the editor.
- Hierarchical matches keep the required parent or child relationships visible.
- Search ignores case and accent differences.
- The interface displays a clear empty state when there are no matches.
- Clearing the search restores the complete checklist.
- Bulk Edit allows selected products to be filtered by name.
- Filtering selected products does not remove or change the actual selection.
- Native product removal buttons continue to work.
- Filters continue working after WordPress creates or replaces an edit row.
- The interface is keyboard accessible and fully translatable.
- Normal use does not add catalog-wide queries.

## Open Questions for Maintainers

1. Is there an existing WooCommerce admin search component that should be reused?
2. Should taxonomy search always be visible, or only appear above a certain number of terms?
3. What hierarchical behavior should be preferred when a parent or child term matches?
4. Should selected-product search filter by product name only, or also by SKU?
5. Is there a recommended event or API for extensions that rebuild taxonomy checklists?

## Conclusion

Taxonomy search and selected-product search are small, focused improvements that would make large WooCommerce catalogs clearer and more efficient to manage. They preserve current behavior, require no frontend changes, and do not modify product data while searching.

The independent prototype can provide a starting point for reviewing the interaction and determining the most appropriate native WooCommerce implementation.
