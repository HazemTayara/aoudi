interface PaginationProps {
  page: number;
  totalPages: number;
  onPageChange: (page: number) => void;
}

function Pagination({ page, totalPages, onPageChange }: PaginationProps) {
  if (totalPages <= 1) return null;

  const pages: number[] = [];
  const start = Math.max(1, page - 2);
  const end = Math.min(totalPages, page + 2);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return (
    <nav>
      <ul className="pagination justify-content-center">
        <li className={`page-item ${page <= 1 ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onPageChange(page - 1)}>السابق</button>
        </li>
        {start > 1 && (
          <>
            <li className="page-item">
              <button className="page-link" onClick={() => onPageChange(1)}>1</button>
            </li>
            {start > 2 && <li className="page-item disabled"><span className="page-link">...</span></li>}
          </>
        )}
        {pages.map(p => (
          <li key={p} className={`page-item ${p === page ? 'active' : ''}`}>
            <button className="page-link" onClick={() => onPageChange(p)}>{p}</button>
          </li>
        ))}
        {end < totalPages && (
          <>
            {end < totalPages - 1 && <li className="page-item disabled"><span className="page-link">...</span></li>}
            <li className="page-item">
              <button className="page-link" onClick={() => onPageChange(totalPages)}>{totalPages}</button>
            </li>
          </>
        )}
        <li className={`page-item ${page >= totalPages ? 'disabled' : ''}`}>
          <button className="page-link" onClick={() => onPageChange(page + 1)}>التالي</button>
        </li>
      </ul>
    </nav>
  );
}

export default Pagination;
