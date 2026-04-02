# UI Components Guidelines

This project uses Blade components under `resources/views/components` to keep UI consistent and prevent component sprawl.

## Naming
- Use one component per primitive (input, button, card, modal, table).
- Prefer semantic nouns: `button`, `input`, `card`, `modal`, `table`.

## Props
- Use `variant` for visual style (preferred). Components may accept legacy aliases but new usage should use `variant`.
- Avoid using `type` for styling. Reserve `type` for actual HTML semantics where applicable.
- Do not create a `class` prop. Use attribute merging (`$attributes->merge([...])`) and pass `class="..."` normally.
- Do not add props that the component does not implement.

## Inputs
- Prefer `<x-input ... />` for all input fields.
- `<x-text-input ... />` is an alias for `<x-input ... />` and should not be used for new work.

## Buttons
- Prefer `<x-button variant="primary|secondary|danger|outline" size="xs|sm|md|lg">...</x-button>`.
- `color="..."` is supported as a legacy alias for `variant`.
- `<x-primary-button>`, `<x-secondary-button>`, and `<x-danger-button>` are wrappers around `<x-button>` for compatibility; prefer `<x-button>` for new work.

## Modals
- Prefer `<x-modal name="..." :show="...">...</x-modal>`.
- If you need Livewire-controlled visibility, use `wire:model="property"` (supported by the modal component).

## Slots
- Use the default slot as the primary content.
- Only add named slots when necessary and avoid introducing aliases for the same slot.

