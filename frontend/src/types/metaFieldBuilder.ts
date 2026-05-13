export type MetaFieldType = 'string' | 'number' | 'boolean' | 'date' | 'select' | 'email' | 'url' | 'phone'

export interface MetaFieldDefinition {
  key: string
  type: MetaFieldType
  options?: string[]
  label?: string
}

export interface MetaFieldOption {
  value: string
  label: string
}

export interface MetaFieldTypeDefinition {
  type: MetaFieldType
  label: string
  description: string
  icon: string
  hasOptions: boolean
  defaultValue: () => unknown
  normalize: (value: unknown) => unknown
  render: (value: unknown, onChange: (val: unknown) => void) => string
}

class MetaFieldTypeBuilder {
  private types: Map<MetaFieldType, MetaFieldTypeDefinition> = new Map()

  constructor() {
    this.registerDefaults()
  }

  private registerDefaults() {
    this.register({
      type: 'string',
      label: 'Texto',
      description: 'Cadena de texto libre',
      icon: 'Aa',
      hasOptions: false,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<input type="text" value="${v ?? ''}" onchange="onChange(this.value)" />`
    })

    this.register({
      type: 'number',
      label: 'Número',
      description: 'Valor numérico (entero o decimal)',
      icon: '#',
      hasOptions: false,
      defaultValue: () => 0,
      normalize: (v) => Number(v) || 0,
      render: (v, onChange) => `<input type="number" value="${v ?? 0}" onchange="onChange(Number(this.value))" />`
    })

    this.register({
      type: 'decimal',
      label: 'Decimal',
      description: 'Número con decimales',
      icon: '.',
      hasOptions: false,
      defaultValue: () => 0.00,
      normalize: (v) => parseFloat(String(v)) || 0.00,
      render: (v, onChange) => `<input type="number" step="0.01" value="${v ?? 0.00}" onchange="onChange(parseFloat(this.value))" />`
    })

    this.register({
      type: 'boolean',
      label: 'Sí/No',
      description: 'Valor booleano (verdadero/falso)',
      icon: '✓',
      hasOptions: false,
      defaultValue: () => false,
      normalize: (v) => Boolean(v),
      render: (v, onChange) => `<button type="button" onclick="onChange(!${v})">${v ? 'Sí' : 'No'}</button>`
    })

    this.register({
      type: 'date',
      label: 'Fecha',
      description: 'Fecha (día/mes/año)',
      icon: '📅',
      hasOptions: false,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<input type="date" value="${v ?? ''}" onchange="onChange(this.value)" />`
    })

    this.register({
      type: 'select',
      label: 'Selección',
      description: 'Lista de opciones predefinidas',
      icon: '▼',
      hasOptions: true,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<select onchange="onChange(this.value)"><option value="">Seleccionar...</option></select>`
    })

    this.register({
      type: 'email',
      label: 'Email',
      description: 'Dirección de correo electrónico',
      icon: '@',
      hasOptions: false,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<input type="email" value="${v ?? ''}" onchange="onChange(this.value)" />`
    })

    this.register({
      type: 'url',
      label: 'URL',
      description: 'Enlace a página web',
      icon: '🔗',
      hasOptions: false,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<input type="url" value="${v ?? ''}" onchange="onChange(this.value)" />`
    })

    this.register({
      type: 'phone',
      label: 'Teléfono',
      description: 'Número de teléfono',
      icon: '📞',
      hasOptions: false,
      defaultValue: () => '',
      normalize: (v) => String(v ?? ''),
      render: (v, onChange) => `<input type="tel" value="${v ?? ''}" onchange="onChange(this.value)" />`
    })
  }

  register(definition: MetaFieldTypeDefinition) {
    this.types.set(definition.type, definition)
  }

  get(type: MetaFieldType): MetaFieldTypeDefinition | undefined {
    return this.types.get(type)
  }

  getAll(): MetaFieldTypeDefinition[] {
    return Array.from(this.types.values())
  }

  getTypes(): MetaFieldTypeDefinition[] {
    return this.getAll()
  }

  getDefaultValue(type: MetaFieldType): unknown {
    const def = this.types.get(type)
    return def ? def.defaultValue() : ''
  }

  normalize(type: MetaFieldType, value: unknown): unknown {
    const def = this.types.get(type)
    return def ? def.normalize(value) : value
  }
}

export const metaFieldTypes = new MetaFieldTypeBuilder()

export function inferFieldType(value: unknown): MetaFieldType {
  if (value === true || value === false) return 'boolean'
  if (typeof value === 'number') {
    return Number.isInteger(value) ? 'number' : 'decimal'
  }
  if (typeof value === 'string') {
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) return 'date'
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'email'
    if (/^https?:\/\//.test(value)) return 'url'
    if (/^\+?[\d\s-]{7,}$/.test(value)) return 'phone'
  }
  return 'string'
}